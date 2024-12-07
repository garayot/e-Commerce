<?php

namespace Auth;

use Database\Database;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Firebase\JWT\JWT;
use PDO;

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
            'exp' => time() + 60 * 60,
            'user_uuid' => $user_uuid,
        ];

        return JWT::encode($payload, $this->jwtSecret, 'HS256');
    }

    private function checkVerificationExpiration($user_uuid)
    {
        $stmt = $this->conn->prepare(
            'SELECT * FROM verification_codes WHERE user_uuid = :user_uuid AND expires_at > NOW()'
        );
        $stmt->bindValue(':user_uuid', $user_uuid, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return ['valid' => true, 'code' => $result['code']];
        } else {
            return ['valid' => false];
        }
    }

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
            error_log(
                "Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}"
            );
            return false;
        }
    }

    // Handle Login
    public function login($email, $password)
    {
        $stmt = $this->conn->prepare(
            'SELECT * FROM users WHERE email = :email'
        );
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if (password_verify($password, $user['password'])) {
                $verificationStatus = $this->checkVerificationExpiration(
                    $user['user_uuid']
                );
                if ($verificationStatus['valid']) {
                    $stmt = $this->conn->prepare(
                        'SELECT * FROM verification_codes WHERE user_uuid = :user_uuid AND expires_at > NOW()'
                    );
                    $stmt->bindValue(
                        ':user_uuid',
                        $user['user_uuid'],
                        PDO::PARAM_STR
                    );
                    $stmt->execute();
                    $verification = $stmt->fetch(PDO::FETCH_ASSOC);

                    if (
                        $verificationStatus['valid'] &&
                        $verification['verified'] == 0
                    ) {
                        return [
                            'error' =>
                                'Please verify your account before logging in',
                        ];
                    }

                    $token = $this->generateJWT($user['user_uuid']);
                    $id = bin2hex(random_bytes(16));
                    $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
                    $stmt = $this->conn->prepare(
                        'INSERT INTO sessiontokens (id, user_uuid, token, expires_at) VALUES (:id, :user_uuid, :token, :expires_at)'
                    );
                    $stmt->bindValue(':id', $id, PDO::PARAM_STR);
                    $stmt->bindValue(
                        ':user_uuid',
                        $user['user_uuid'],
                        PDO::PARAM_STR
                    );
                    $stmt->bindValue(':token', $token, PDO::PARAM_STR);
                    $stmt->bindValue(
                        ':expires_at',
                        $expires_at,
                        PDO::PARAM_STR
                    );
                    $stmt->execute();

                    return ['message' => 'Login successful', 'token' => $token];
                } else {
                    date_default_timezone_set('UTC');

                    $verification_code = random_int(100000, 999999);
                    $expires_at = date('Y-m-d H:i:s', strtotime('+24 hours'));

                    $stmt = $this->conn->prepare(
                        'INSERT INTO verification_codes (id, user_uuid, code, expires_at) VALUES (UUID(), :user_uuid, :code, :expires_at)'
                    );
                    $stmt->bindValue(
                        ':user_uuid',
                        $user['user_uuid'],
                        PDO::PARAM_STR
                    );
                    $stmt->bindValue(
                        ':code',
                        $verification_code,
                        PDO::PARAM_INT
                    );
                    $stmt->bindValue(
                        ':expires_at',
                        $expires_at,
                        PDO::PARAM_STR
                    );

                    if ($stmt->execute()) {
                        if (
                            $this->sendVerificationEmail(
                                $email,
                                $verification_code
                            )
                        ) {
                            return [
                                'message' => 'Verification code sent',
                                'user_uuid' => $user['user_uuid'],
                            ];
                        } else {
                            return [
                                'error' => 'Failed to send verification code',
                            ];
                        }
                    } else {
                        return [
                            'error' => 'Failed to generate verification code',
                        ];
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
        $stmt = $this->conn->prepare(
            'SELECT * FROM verification_codes WHERE user_uuid = :user_uuid AND code = :code AND expires_at > NOW()'
        );
        $stmt->bindValue(':user_uuid', $user_uuid, PDO::PARAM_STR);
        $stmt->bindValue(':code', $verification_code, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $token = $this->generateJWT($user_uuid);
            $id = bin2hex(random_bytes(16));
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $stmt = $this->conn->prepare(
                'INSERT INTO sessiontokens (id, user_uuid, token, expires_at) VALUES (:id, :user_uuid, :token, :expires_at)'
            );
            $stmt->bindValue(':id', $id, PDO::PARAM_STR);
            $stmt->bindValue(':user_uuid', $user_uuid, PDO::PARAM_STR);
            $stmt->bindValue(':token', $token, PDO::PARAM_STR);
            $stmt->bindValue(':expires_at', $expires_at, PDO::PARAM_STR);
            $stmt->execute();

            $stmt = $this->conn->prepare(
                'UPDATE verification_codes SET used = 1, verified = 1 WHERE user_uuid = :user_uuid AND code = :code'
            );
            $stmt->bindValue(':user_uuid', $user_uuid, PDO::PARAM_STR);
            $stmt->bindValue(':code', $verification_code, PDO::PARAM_INT);
            $stmt->execute();

            return ['message' => 'Verification successful', 'token' => $token];
        } else {
            return ['error' => 'Invalid or expired verification code'];
        }
    }

    public function register(
        $first_name,
        $last_name,
        $email,
        $phone_number,
        $address,
        $password
    ) {
        // Validate input
        if (
            empty($first_name) ||
            empty($last_name) ||
            empty($email) ||
            empty($phone_number) ||
            empty($address) ||
            empty($password)
        ) {
            return ['error' => 'Invalid input data'];
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $user_uuid = bin2hex(random_bytes(16));

        // Check if the email is already in use
        $stmt = $this->conn->prepare(
            'SELECT * FROM users WHERE email = :email'
        );
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return ['error' => 'Email is already in use'];
        }

        // Check if the phone number is already in use
        $stmt = $this->conn->prepare(
            'SELECT * FROM users WHERE phone_number = :phone_number'
        );
        $stmt->bindValue(':phone_number', $phone_number, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return ['error' => 'Phone number is already in use'];
        }

        // If both email and phone number are unique, insert the new user
        $stmt = $this->conn->prepare(
            'INSERT INTO users (user_uuid, first_name, last_name, email, phone_number, address, password) VALUES (:user_uuid, :first_name, :last_name, :email, :phone_number, :address, :password)'
        );
        $stmt->bindValue(':user_uuid', $user_uuid, PDO::PARAM_STR);
        $stmt->bindValue(':first_name', $first_name, PDO::PARAM_STR);
        $stmt->bindValue(':last_name', $last_name, PDO::PARAM_STR);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':phone_number', $phone_number, PDO::PARAM_STR);
        $stmt->bindValue(':address', $address, PDO::PARAM_STR);
        $stmt->bindValue(':password', $hashed_password, PDO::PARAM_STR);

        if ($stmt->execute()) {
            return ['message' => 'User registered successfully'];
        } else {
            return ['error' => 'Failed to register user'];
        }
    }

    public function logout()
    {
        $headers = apache_request_headers();

        if (
            isset($headers['Authorization']) &&
            preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)
        ) {
            $token = $matches[1];

            $stmt = $this->conn->prepare(
                'SELECT * FROM sessiontokens WHERE token = :token'
            );
            $stmt->bindValue(':token', $token, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                $stmt = $this->conn->prepare(
                    'DELETE FROM sessiontokens WHERE token = :token'
                );
                $stmt->bindValue(':token', $token, PDO::PARAM_STR);

                if ($stmt->execute()) {
                    return ['message' => 'Logout successful'];
                } else {
                    return ['error' => 'Logout failed due to a server issue'];
                }
            } else {
                return ['error' => 'Invalid or expired token'];
            }
        } else {
            return ['error' => 'Authorization token not provided or invalid'];
        }
    }
}
