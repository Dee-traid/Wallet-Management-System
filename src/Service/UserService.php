<?php

namespace src\Service;

use src\Repository\UserRepository;
use src\Models\User;
use src\Utility\UtilityHelper;
use DateTimeImmutable;
use PDOException;
use Exception;
use Throwable;

class UserService{
    private UserRepository $userRepository;
    private MailService $mailService;

    public function __construct(UserRepository $userRepository, MailService $mailService){
        $this->userRepository = $userRepository;
        $this->mailService = $mailService;
    }


    private function sendEmailVerification(User $user){
        try {
            $token = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $token);
            $createdAt = new DateTimeImmutable();
            $expiresAt = new DateTimeImmutable('+2 hours');
            $userId = $user->getID();
            $email = $user->getEmail();
            $fullName = $user->getFullName();

            $this->userRepository->saveVerificationToken($userId,$email, $tokenHash, $createdAt, $expiresAt);
            $link = rtrim($_ENV['APP_URL'], '/') . '/src/Router/Router.php?action=verify-email&token=' . $token;

            return $this->mailService->sendEmailVerification($email, $fullName, $link);
        } catch (Throwable $e) {
            error_log("Error sending verification link" . $e->getMessage());
            UtilityHelper::sendJson('error', "Error sending verification link", 500);
        }
       
    }

    public function verifyEmail(string $token): void{
        try {
            $record = $this->userRepository->findTokenVerification(hash('sha256', $token));
            $timeStamp = new DateTimeImmutable();
            if($record === null){
                UtilityHelper::sendJson('error', "Invalid/ Expired token", 400);
            }

            if($timeStamp > new DateTimeImmutable($record['expires_at'])){
                UtilityHelper::sendJson('error', "Verification link expired, Kindly request for a new link", 400);
            }

            $this->userRepository->markEmailVerified($record['user_id']);
            UtilityHelper::sendJson('success', "Email has been verified", 200);
        } catch (Throwable $e) {
            error_log("An error occurred in verifying Email" . $e->getMessage());
            UtilityHelper::sendJson('error', "An error occurred while verifying email, Please try again" , 500);
        }
    }

    public function resendEmailVerification(string $email): void{
        try {
            $user = $this->userRepository->findUserByEmail($email);
            if($user === null && !$user->getEmailVerified()){
                $this->sendEmailVerification($user);
            }
            UtilityHelper::sendJson('success', "Account with $user does not exist", 200);
        } catch (Throwable $e) {
            error_log("Resend verification error:" . $e->getMessage);
            UtilityHelper::sendJson('error', "An error occurred", 500);
        }
    }

    public function registerUser(string $fullName, string $email, string $password, string $walletTransactionPin){
        $email = strtolower(trim($email));
        try {
            $existingUser = $this->userRepository->findUserByEmail($email);
            if ($existingUser !== null) {
                UtilityHelper::sendJson('error', 'Email already exists, Please sign into your account', 409);
            }

            $id = uniqid('user-',true);
            $passwordHash = UtilityHelper::hashValue($password);
            $walletTransactionPinHash = UtilityHelper::hashValue($walletTransactionPin);
            $timeStamp = new DateTimeImmutable();

            $user = new User($id, 
                $fullName, 
                $email, 
                $passwordHash, 
                $walletTransactionPinHash, 
                true, 
                false, 
                $timeStamp, 
                $timeStamp
            );

            $this->userRepository->createUser($user);
            try {
                $this->sendEmailVerification($user);
            } catch (Throwable $e) {
                error_log('Email verification failed: ' . $e->getMessage());
            }

            return UtilityHelper::sendJson('success', "User created successfully", 201);
        } catch (Throwable $e) {
            error_log('Registration error: ' . $e->getMessage());
            UtilityHelper::sendJson('error', "An error occurred creating user", 500);
        }
    }

}



// private function userRegistration($data){
    //     try{

    //         $emailCheck = $this->userRepository->findUserByEmail($email);
    //         if($emailCheck !== null){
    //             UtilityHelper::sendJson('error', "email already exist, Kindly Sign up", 409);
    //         }

    //         $id = uniqid('user-', true);
    //         $passwordHash = password_hash($password, PASSWORD_BCRYPT,['cost' => 12]);
    //         $timeStamp = date('Y-m-d H:i:s');
    //         $WalletpinHash = UtilityHelper::validateWalletPinHash($walletTransactionPinHash);

    //         $user = new User($id, $fullName, $email, $passwordHash, $walletpinHash, (bool) true, (bool) false, $timeStamp, $timeStamp);
    //         $register = $this->userRepository->createUser($user);

    //         UtilityHelper::sendJson("sucess", "Registration Successful", 201);

    //     }catch(PDOException $e){
    //         error_log('Registration DB error: ' . $e->getMessage());
    //         UtilityHelpers::sendJson('error', 'Something went wrong, please try again', 500);
    //     }catch (Throwable $e) {
    //         error_log('Registration error: ' . $e->getMessage());
    //         UtilityHelpers::sendJson('error', 'Registration not successful, Try again..', 500);
    //     }
    // }

