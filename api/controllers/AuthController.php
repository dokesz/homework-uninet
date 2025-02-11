<?php

use Phalcon\Encryption\Security;

class AuthController extends BaseController
{
    public function loginAction()
    {
        $request = $this->request->getJsonRawBody();

        if (!isset($request->email) || !isset($request->password)) {
            return $this->response->setJsonContent([
                'success' => false,
                'message' => 'Email and password are required'
            ]);
        }

        $user = $this->db->query(
            "SELECT * FROM users WHERE email = :email",
            ['email' => $request->email]
        )->fetch();

        $security = new Security();
        $isPasswordValid = false;

        if ($user) {
            if (password_needs_rehash($user->password, PASSWORD_DEFAULT)) {
                $isPasswordValid = $request->password === $user->password;
            } else {
                $isPasswordValid = $security->checkHash($request->password, $user->password);
            }
        }

        if ($isPasswordValid) {
            $token = bin2hex(random_bytes(16));

            $this->db->execute(
                "INSERT INTO user_sessions (user_id, token, expires_at) VALUES (:user_id, :token, :expires_at)",
                [
                    'user_id' => (int)$user->id,
                    'token' => $token,
                    'expires_at' => date('Y-m-d H:i:s', time() + 3600)
                ]
            );

            return $this->response->setJsonContent([
                'success' => true,
                'token' => $token
            ]);
        }

        return $this->response->setJsonContent([
            'success' => false,
            'message' => 'Invalid email or password'
        ]);
    }

    public function logoutAction()
    {
        $this->db->execute(
            "DELETE FROM user_sessions WHERE token = :token",
            ['token' => $this->request->getHeader('Authorization')]
        );

        return $this->response->setJsonContent([
            'success' => true
        ]);
    }
}
