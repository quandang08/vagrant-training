<?php

require_once 'BaseModel.php';

class UserModel extends BaseModel {

    public function findUserById($id) {
        $sql = "SELECT * FROM users WHERE id = ?";
        $stmt = self::$_connection->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $user = $res->fetch_assoc();
        $stmt->close();
        return $user ? $user : null;
    }

    public function findUser($keyword) {
        $like = '%' . $keyword . '%';
        $sql = "SELECT * FROM users WHERE username LIKE ? OR email LIKE ?";
        $stmt = self::$_connection->prepare($sql);
        $stmt->bind_param('ss', $like, $like);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($r = $res->fetch_assoc()) $rows[] = $r;
        $stmt->close();
        return $rows;
    }

    /**
     * Authentication user
     * @param $userName
     * @param $password
     * @return array|null
     */
    public function auth($userName, $password) {
        // NOTE: your DB currently stores MD5(password) from earlier steps.
        // If you later migrate to password_hash(), change this accordingly.
        $md5Password = md5($password);
        $sql = "SELECT * FROM users WHERE username = ? AND password = ?";
        $stmt = self::$_connection->prepare($sql);
        $stmt->bind_param('ss', $userName, $md5Password);
        $stmt->execute();
        $res = $stmt->get_result();
        $user = $res->fetch_assoc();
        $stmt->close();
        return $user ? $user : null;
    }

    public function deleteUserById($id) {
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = self::$_connection->prepare($sql);
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function updateUser($input) {
        // Expecting keys: id, username, password (optionally full_name, email)
        $id = (int)$input['id'];
        $username = $input['username'] ?? null;
        $password = isset($input['password']) ? md5($input['password']) : null;
        $full_name = $input['full_name'] ?? null;
        $email = $input['email'] ?? null;

        // Build dynamic update safely
        $sets = [];
        $types = '';
        $values = [];

        if ($username !== null) { $sets[] = "username = ?"; $types .= 's'; $values[] = $username; }
        if ($password !== null) { $sets[] = "password = ?"; $types .= 's'; $values[] = $password; }
        if ($full_name !== null) { $sets[] = "full_name = ?"; $types .= 's'; $values[] = $full_name; }
        if ($email !== null) { $sets[] = "email = ?"; $types .= 's'; $values[] = $email; }

        if (empty($sets)) return false;

        $sql = "UPDATE users SET " . implode(', ', $sets) . " WHERE id = ?";
        $types .= 'i';
        $values[] = $id;

        $stmt = self::$_connection->prepare($sql);
        $stmt->bind_param($types, ...$values);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function insertUser($input) {
        // Expecting username, password, (optionally full_name, email)
        $username = $input['username'];
        $password = md5($input['password']);
        $full_name = $input['full_name'] ?? null;
        $email = $input['email'] ?? null;

        $sql = "INSERT INTO users (username, password, full_name, email) VALUES (?, ?, ?, ?)";
        $stmt = self::$_connection->prepare($sql);
        $stmt->bind_param('ssss', $username, $password, $full_name, $email);
        $ok = $stmt->execute();
        $insertId = $stmt->insert_id;
        $stmt->close();
        return $ok ? $insertId : false;
    }

    public function getUsers($params = []) {
        if (!empty($params['keyword'])) {
            $like = '%' . $params['keyword'] . '%';
            $sql = "SELECT * FROM users WHERE username LIKE ? OR email LIKE ?";
            $stmt = self::$_connection->prepare($sql);
            $stmt->bind_param('ss', $like, $like);
            $stmt->execute();
            $res = $stmt->get_result();
            $rows = [];
            while ($r = $res->fetch_assoc()) $rows[] = $r;
            $stmt->close();
            return $rows;
        } else {
            $sql = "SELECT * FROM users";
            $res = $this->select($sql);
            return $res;
        }
    }
}
