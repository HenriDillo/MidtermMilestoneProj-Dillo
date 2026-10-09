<?php
// User - database work for the users table.
// Every query uses prepared statements (:name placeholders), so typed text can never
// become SQL code. That is how we stop SQL injection.

class User
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function usernameExists($username)
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM users WHERE username = :username');
        $stmt->execute([':username' => $username]);
        return $stmt->fetchColumn() > 0;
    }

    public function emailExists($email)
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM users WHERE email = :email');
        $stmt->execute([':email' => $email]);
        return $stmt->fetchColumn() > 0;
    }

    // Save a new member. The password is turned into a hash first,
    // so the real password is never stored anywhere.
    public function create($username, $email, $password)
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->pdo->prepare(
            'INSERT INTO users (username, email, password_hash) VALUES (:username, :email, :hash)'
        );
        $stmt->execute([':username' => $username, ':email' => $email, ':hash' => $hash]);

        return (int) $this->pdo->lastInsertId();
    }

    // Check a login. $login can be a username or an email.
    // Returns the user row if the password is right, otherwise null.
    public function verifyLogin($login, $password)
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, username, password_hash FROM users
             WHERE username = :login_name OR email = :login_email
             LIMIT 1'
        );
        $stmt->execute([':login_name' => $login, ':login_email' => $login]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            return $user;
        }
        return null;
    }
}
