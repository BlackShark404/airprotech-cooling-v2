<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\UserModel;
use Config\Database;
use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

Database::getInstance()->getConnection();

$userModel = new UserModel;

// Check if any admin user exists
$adminExists = $userModel->getByRole('admin');

// If no admin exists, create one from environment variables
if (empty($adminExists)) {
    // Get admin details from environment variables
    $adminProfileUrl = $_ENV['ADMIN_PROFILE_URL'];
    $adminFirstName = $_ENV['ADMIN_FIRST_NAME'];
    $adminLastName = $_ENV['ADMIN_LAST_NAME'];
    $adminEmail = $_ENV['ADMIN_EMAIL'];
    $adminPassword = $_ENV['ADMIN_PASSWORD'];
    $adminRoleId = (int) $_ENV['ADMIN_ROLE_ID'];

    // Hash the password
    $hashedPassword = $userModel->hashPassword($adminPassword);
    
    // Create admin user
    $userModel->createUser([
        'ua_profile_url' => $adminProfileUrl,
        'ua_first_name' => $adminFirstName,
        'ua_last_name' => $adminLastName,
        'ua_email' => $adminEmail,
        'ua_hashed_password' => $hashedPassword,
        'ua_role_id' => $adminRoleId
    ]);


    // Define user details
    $userProfileUrl = 'https://ui-avatars.com/api/?name=Khen+Sorela&background=00cc99&color=fff&size=128';
    $firstName = 'Khen';
    $lastName = 'Sorela';
    $email = 'khen@gmail.com';
    $password = 'ad';  // plaintext password
    $roleId = 1;  // User role

    // Hash the password
    $hashedPassword = $userModel->hashPassword($password);

    // Create user
    $userModel->createUser([
        'ua_profile_url' => $userProfileUrl,
        'ua_first_name' => $firstName,
        'ua_last_name' => $lastName,
        'ua_email' => $email,
        'ua_hashed_password' => $hashedPassword,
        'ua_role_id' => $roleId
    ]);

    // Define technician details
    $userProfileUrl = 'https://ui-avatars.com/api/?name=Arlon+Rondina&background=80ffbf&color=fff&size=128';
    $firstName = 'Arlon';
    $lastName = 'Rondina';
    $email = 'arlon@gmail.com';
    $password = 'ad';  // plaintext password
    $roleId = 2;  // User role

    // Create user
    $userModel->createUser([
        'ua_profile_url' => $userProfileUrl,
        'ua_first_name' => $firstName,
        'ua_last_name' => $lastName,
        'ua_email' => $email,
        'ua_hashed_password' => $hashedPassword,
        'ua_role_id' => $roleId
    ]);

    // Hash the password
    $hashedPassword = $userModel->hashPassword($password);
    
    // Log the creation of admin account (optional)
    error_log('Admin, Technician and User account has been created');
}
