<?php

namespace Auth;

require '../vendor/autoload.php';

use database\Database;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Firebase\JWT\JWT;

class UserAuthentication
{
    private $conn;
    private $mail;
    private $jwtSecret;

    public function __construct(Database $db)
    {
        $this->conn = $db->getConnection();
        $this->mail = new PHPMailer(true);
        $this->jwtSecret = getenv('JWT_SECRET') ?: bin2hex(random_bytes(32));
    }

    // Function to generate a JWT token
    private function generateJWT($user_uuid)
    {
        $payload = [
            'iat' => time(),
            'exp' => time() + (60 * 60),
            'user_uuid' => $user_uuid,
        ];

        return JWT::encode($payload, $this->jwtSecret, 'HS256');
    }

    private function checkVerificationExpiration($user_uuid)
    {
        $stmt = $this->conn->prepare("SELECT * FROM verification_codes WHERE user_uuid = ? AND expires_at > NOW()");
        $stmt->bind_param('s', $user_uuid);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            return ['valid' => true, 'code' => $result->fetch_assoc()['code']];
        } else {
            return ['valid' => false];
        }
    }

    // Send Verification Email
    private function sendVerificationEmail($email, $code)
    {
        try {
            $this->mail->isSMTP();
            $this->mail->Host = 'smtp.gmail.com';
            $this->mail->SMTPAuth = true;
            $this->mail->Username = 'balotcfpro@gmail.com';
            $this->mail->Password = 'opgq cepd tibf cved';
            $this->mail->SMTPSecure = 'tls';
            $this->mail->Port = 587;

            $this->mail->setFrom('csustore@gmail.com', 'CSU STORE');
            $this->mail->addAddress($email);

            $this->mail->isHTML(true);
            $this->mail->Subject = 'Your Login Verification Code';
            $this->mail->Body = "Your verification code is: {$code}";

            $this->mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}");
            return false;
        }
    }

    // Handle Login
    public function login($email, $password)
    {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                // Check if the verification code is valid
                $verificationStatus = $this->checkVerificationExpiration($user['user_uuid']);
                if ($verificationStatus['valid']) {

                    $token = $this->generateJWT($user['user_uuid']);
                    $id = bin2hex(random_bytes(16));
                    $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
                    $stmt = $this->conn->prepare("INSERT INTO session_token (id, user_uuid, token, expires_at) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param('ssss', $id, $user['user_uuid'], $token, $expires_at);
                    $stmt->execute();

                    return ['message' => 'Login successful', 'token' => $token];
                } else {
                    // Create new verification code if not valid
                    date_default_timezone_set('UTC');

                    $verification_code = random_int(100000, 999999);
                    $expires_at = date('Y-m-d H:i:s', strtotime('+10 minutes'));

                    // Generate a UUID for the verification code
                    $stmt = $this->conn->prepare("INSERT INTO verification_codes (id, user_uuid, code, expires_at) VALUES (UUID(), ?, ?, ?)");
                    $stmt->bind_param('sss', $user['user_uuid'], $verification_code, $expires_at);

                    if ($stmt->execute()) {
                        if ($this->sendVerificationEmail($email, $verification_code)) {
                            return ['message' => 'Verification code sent', 'user_uuid' => $user['user_uuid']];
                        } else {
                            return ['error' => 'Failed to send verification code'];
                        }
                    } else {
                        return ['error' => 'Failed to generate verification code'];
                    }
                }
            } else {
                return ['error' => 'Invalid password'];
            }
        } else {
            return ['error' => 'User not found'];
        }
    }

    // Verify Code
    public function verifyCode($user_uuid, $verification_code)
    {
        $stmt = $this->conn->prepare("SELECT * FROM verification_codes WHERE user_uuid = ? AND code = ? AND expires_at > NOW()");
        $stmt->bind_param('ss', $user_uuid, $verification_code);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $token = $this->generateJWT($user_uuid);
            $id = bin2hex(random_bytes(16));
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $stmt = $this->conn->prepare("INSERT INTO session_token (id,user_uuid, token, expires_at) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('ssss', $id, $user_uuid, $token, $expires_at);
            $stmt->execute();

            return ['message' => 'Verification successful', 'token' => $token];
        } else {
            return ['error' => 'Invalid or expired verification code'];
        }
    }

    public function register($first_name, $last_name, $email, $phone_number, $address, $password)
    {
        if (empty($first_name) || empty($last_name) || empty($email) || empty($phone_number) || empty($address) || empty($password)) {
            return ['error' => 'Invalid input data'];
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $user_uuid = bin2hex(random_bytes(16)); // Generate a unique UUID for the user

        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ? OR phone_number = ?");
        $stmt->bind_param('ss', $email, $phone_number);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            // If the user doesn't exist, insert a new record
            $stmt = $this->conn->prepare("INSERT INTO users (user_uuid, first_name, last_name, email, phone_number, address, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('sssssss', $user_uuid, $first_name, $last_name, $email, $phone_number, $address, $hashed_password);

            if ($stmt->execute()) {
                return ['message' => 'User registered successfully'];
            } else {
                return ['error' => 'Failed to register user'];
            }
        } else {
            return ['error' => 'User already exists'];
        }
    }

    // Logout function
    public function logout()
    {
        // Get the Authorization header
        $headers = apache_request_headers();

        // Check if the Authorization header is present and contains a Bearer token
        if (isset($headers['Authorization']) && preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
            $token = $matches[1]; // Extract the token

            // Prepare the SQL statement
            $stmt = $this->conn->prepare("DELETE FROM session_token WHERE token = ?");
            $stmt->bind_param('s', $token);

            if ($stmt->execute()) {
                return ['message' => 'Logout successful'];
            } else {
                return ['error' => 'Logout failed'];
            }
        } else {
            return ['error' => 'Authorization token not provided or invalid'];
        }
    }
}
