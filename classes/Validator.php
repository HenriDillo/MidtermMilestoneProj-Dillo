<?php
// Validator - ALL the server-side rules in one place.
// The same rules are repeated in assets/js/app.js to give quick feedback in the browser.
// But a browser can be bypassed, so THIS file is the one that really protects the site.
//
// Each method returns an array of errors like ['title' => 'message'].
// An empty array means everything is valid.

class Validator
{
    public static function registration($username, $email, $password, $confirm)
    {
        $errors = [];

        if (!preg_match('/^[A-Za-z0-9_]{3,20}$/D', $username)) {
            $errors['username'] = 'Username must be 3 to 20 characters: letters, numbers and underscore only.';
        }
        if ($email === '' || strlen($email) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address.';
        }
        if (strlen($password) < 8 || strlen($password) > 72
            || !preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            $errors['password'] = 'Password must be 8 to 72 characters with at least one letter and one number.';
        }
        if ($confirm !== $password) {
            $errors['confirm_password'] = 'Passwords do not match.';
        }
        return $errors;
    }

    public static function login($login, $password)
    {
        $errors = [];
        if ($login === '') {
            $errors['login'] = 'Please enter your username or email.';
        }
        if ($password === '') {
            $errors['password'] = 'Please enter your password.';
        }
        return $errors;
    }

    // $data = the recipe fields, $ingredients = list of ingredient texts,
    // $validCategoryIds = ids that really exist in the categories table.
    public static function recipe(array $data, array $ingredients, array $validCategoryIds)
    {
        $errors = [];

        $titleLength = mb_strlen($data['title']);
        if ($titleLength < 3 || $titleLength > 100) {
            $errors['title'] = 'Title must be 3 to 100 characters.';
        }

        $descLength = mb_strlen($data['description']);
        if ($descLength < 10 || $descLength > 300) {
            $errors['description'] = 'Description must be 10 to 300 characters.';
        }

        if (!in_array($data['category_id'], $validCategoryIds)) {
            $errors['category_id'] = 'Please choose a category.';
        }

        $cook = $data['cook_time_minutes'];
        if (!ctype_digit($cook) || (int) $cook < 1 || (int) $cook > 1440) {
            $errors['cook_time_minutes'] = 'Cooking time must be a whole number from 1 to 1440 minutes.';
        }

        $servings = $data['servings'];
        if (!ctype_digit($servings) || (int) $servings < 1 || (int) $servings > 100) {
            $errors['servings'] = 'Servings must be a whole number from 1 to 100.';
        }

        if (count($ingredients) === 0) {
            $errors['ingredients'] = 'Add at least one ingredient.';
        } elseif (count($ingredients) > 30) {
            $errors['ingredients'] = 'You can add up to 30 ingredients.';
        } else {
            foreach ($ingredients as $ingredient) {
                if (mb_strlen($ingredient) > 100) {
                    $errors['ingredients'] = 'Each ingredient must be 100 characters or less.';
                    break;
                }
            }
        }

        $stepsLength = mb_strlen($data['steps']);
        if ($stepsLength < 20 || $stepsLength > 5000) {
            $errors['steps'] = 'Cooking steps must be 20 to 5000 characters.';
        }

        return $errors;
    }

    public static function comment($body)
    {
        $errors = [];
        $length = mb_strlen($body);
        if ($length < 2 || $length > 500) {
            $errors['body'] = 'Comment must be 2 to 500 characters.';
        }
        return $errors;
    }
}
