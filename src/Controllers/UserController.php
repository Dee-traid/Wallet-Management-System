<?php
namespace src\Controllers;

use src\Service\UserService;
use src\Utility\UtilityHelper;
use PDO;
use PDOException;

class UserController{
    private UserService $userService;

    public function __construct(UserService $userService) {
        $this->userService = $userService;
    }

    public function register(){
            $json = file_get_contents('php://input');
            $data = json_decode($json, true); 

            $fullName = trim($data['full_name'] ?? '');
            $email = strtolower(trim($data['email'] ?? ''));
            $password = trim($data['password'] ?? '');
            $confirmPassword = trim($data['confirm_password'] ?? '');
            $walletTransactionPin = trim($data['wallet_transaction_pin']?? '');

            $requiredFields = [
                'full_name' =>$fullName, 
                'email' => $email, 
                'password' => $password, 
                'confirm_password' => $confirmPassword,
                'wallet_transaction_pin' => $walletTransactionPin
             ];
             $this->registrationInputValidation($requiredFields);

             $this->userService->registerUser($fullName, $email, $password, $walletTransactionPin);
    }
   
        private function registrationInputValidation(array $fields){

            foreach($fields as $value => $field){
                if(empty($field)){
                    UtilityHelper::sendJson('error', "Field $value must not be empty", 400);
                }
            }

            if(!filter_var($fields['email'], FILTER_VALIDATE_EMAIL)){
                UtilityHelper::sendJson('error', "Invalid email address", 400);
            }

            $passwordRequirement = "/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/";
            if(!preg_match($passwordRequirement, $fields['password'])){
                UtilityHelper::sendJson('error',  "Password must be at least 8 characters and include a letter and a number", 400);
            }

            if($fields['password'] !== $fields['confirm_password']){
                UtilityHelper::sendJson('error', "Password does not match", 400);
            }
            
            $this->validateWalletPin($fields['wallet_transaction_pin']);
    }

    private function validateWalletPin($pin){
        $blocked = ['121212', '112233'];

        if(!preg_match('/^\d{6}$/', $pin)){
            UtilityHelper::sendJson('error', "pin must contain 6 digits", 400);
        }

        if (preg_match('/^(\d)\1{5}$/', $pin)) {
            UtilityHelper::sendJson('error', "Repeated digits are not allowed.", 400);
        }

        if (str_contains('0123456789', $pin) || str_contains('09876543210', $pin) || in_array($pin, $blocked, true)) {
            UtilityHelper::sendJson('error', 'PIN must not be sequential or a common pattern', 400);
        }

    }

    public function verifyEmail(){
        $token = trim($_GET['token'] ?? '');

        if(!preg_match('/^[a-f0-9]{64}$/', $token)){
            UtilityHelper::sendJson('error', 'Invalid verification link', 400);
            return;
        }

        $this->userService->verifyEmail($token);
    }

    public function resendVerification(){
        $data = json_decode(file_get_contents('php://input', true) ?? []);
        $email = trim(strtolower($data['email']));
        
        if(!filter_var($email, FILTER_VaLIDATE_EMAIL)){
            UtilityHelper::sendJson('error', "Invalid email address", 400);
        }

        $this->userService->resendEmailVerification($email);
    }

    
    // public function Login(){
    //     try{
    //         $json = file_get_contents('php://input');
    //         $data = json_decode($json, true);

    //         if (!isset($data['csrf_token'] )|| !verifyCsrfToken($data['csrf_token'])) {
	// 			self::sendJSON("error", "Security session expired. Kindly refresh.", 403);
	// 		}


    //         $email = strtolower(trim($data['email'] ?? ''));
    //         $password = $data['password'] ?? '';

    //         if($email === '' || $password === ''){
    //             UtilityHelper::sendJson('error', "Field must not be empty", 401);
    //         }

    //         if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    //             UtilityHelper::sendJson('error', 'Invalid email address', 400);
    //         }

    //         $userExists = $this->user->findUserByEmail($email);
    //         if(!$userExists){
    //             UtilityHelper::sendJson('error', "Email does not exists, Kindly sign Up");
    //         }

    //         $user = $this->user->findUserByEmail($email);
    //         if($user === null || !password_verify($password, $user->getPassword())){
    //             UtilityHelpers::sendJson('error', 'Invalid email or password', 401);
    //         }

    //         if (!$user->isActive()) {
    //             UtilityHelpers::sendJson('error', 'Account is disabled', 403);
    //         }

    //         UtilityHelpers::sendJson('success', 'Login successful', 200, [
    //             'token' => $token,
    //             'user'  => [
    //                 'id'        => $user->getId(),
    //                 'full_name' => $user->getFullName(),
    //                 'email'     => $user->getEmail(),
    //             ],
    //         ]);
    //     }catch (Throwable $e) {
    //         error_log('Login error: ' . $e->getMessage());
    //         UtilityHelpers::sendJson('error', 'Something went wrong, please try again', 500);
    //     }

    // }

}