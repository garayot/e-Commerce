<?php

namespace Auth;

use database\Database;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class PasswordReset
{
    private $conn;
    private $mail;

    public function __construct(Database $db)
    {
        $this->conn = $db->getConnection();
        $this->mail = new PHPMailer(true);
    }

    private function sendEmail($email, $reset_token)
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

            // Generate the reset link
            $reset_link = "http://localhost:8080/Ecommerce-User/utils/reset-password.php?token=" . $reset_token;
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Password Reset Request';
            $this->mail->Body = "Click the following link to reset your password: <a href='{$reset_link}'>Reset Password</a>";

            $this->mail->send();
        } catch (Exception $e) {
            error_log("Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}");
        }
    }

    public function requestPasswordReset($email)
    {

        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $user_uuid = $user['user_uuid'];


            $reset_token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $stmt = $this->conn->prepare("INSERT INTO session_token (id, user_uuid, token, expires_at) VALUES (UUID(), ?, ?, ?)");
            $stmt->bind_param('sss', $user_uuid, $reset_token, $expires_at);

            if ($stmt->execute()) {

                $this->sendEmail($email, $reset_token);

                return ['message' => 'Password reset link sent to email'];
            } else {
                return ['error' => 'Failed to store reset token'];
            }
        } else {
            return ['error' => 'Email not found'];
        }
    }


    public function resetPassword($data)
    {

        $headers = getallheaders();
        $bearerToken = null;

        if (isset($headers['Authorization'])) {

            $matches = [];
            if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
                $bearerToken = $matches[1];
            }
        }


        $bodyToken = isset($data['token']) ? $data['token'] : null;


        $tokenToValidate = $bodyToken ?? $bearerToken;

        if ($tokenToValidate) {
            $stmt = $this->conn->prepare("SELECT * FROM session_token WHERE token = ? AND expires_at > NOW()");
            $stmt->bind_param('s', $tokenToValidate);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $session = $result->fetch_assoc();
                $user_uuid = $session['user_uuid'];


                $hashed_password = password_hash($data, PASSWORD_DEFAULT);


                $stmt = $this->conn->prepare("UPDATE users SET password = ? WHERE user_uuid = ?");
                $stmt->bind_param('ss', $hashed_password, $user_uuid);

                if ($stmt->execute()) {

                    $stmt = $this->conn->prepare("DELETE FROM session_token WHERE token = ?");
                    $stmt->bind_param('s', $tokenToValidate);
                    $stmt->execute();

                    return ['message' => 'Password reset successful'];
                } else {
                    return ['error' => 'Failed to reset password'];
                }
            } else {
                return ['error' => 'Invalid or expired token'];
            }
        } else {
            return ['error' => 'No token provided'];
        }
    }
}
