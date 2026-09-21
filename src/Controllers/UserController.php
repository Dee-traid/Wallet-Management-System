<?php

class UserController{
    private $userService;

    public function __construct(UserService $userService){
        $this->userService = $userService;
    }

    public function userRegistration($data){
        try{
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            if($contentType !== 'application/json'){
                $input = json_decode(file_get_contents('php://input'), true);
                $data = is_array($input) ? $input : [];
            }            
                $fullName = $data[''];
                $email = $data[''];
                $passwordHash = $data[''];
                $walletTransactionPin = $data[''];

                $registration = $this->userService->userRegistration($fullName, $email, $passwordHash, $walletTransactionPin);
                http_response_code(200);
                header('Content-Type: application/json');
                echo json_encode(['message' => 'User registered successfully', 'user' => $registration]);

        }catch(Exception $e){
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

}