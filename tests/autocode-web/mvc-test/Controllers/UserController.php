<?php
require_once __DIR__."./../Models/User.php";
use AcWeb\Annotaions\AcWebRoute;
use AcWeb\Annotaions\AcWebValueFromBody;
use AcWeb\Annotaions\AcWebValueFromHeader;
use AcWeb\Annotaions\AcWebValueFromPath;
use AcWeb\Annotaions\AcWebValueFromQuery;

#[AcWebRoute('/user')]
class UserController {

    #[AcWebRoute(path: '/create', method: 'POST')]
    public function createUser(#[AcWebValueFromBody] User $user): void {
        echo json_encode([
            'message' => 'User created',
            'data' => $user
        ]);
    }

    #[AcWebRoute(path: '/get', method: 'GET')]
    // #[Authorize(['admin'])]
    // #[Produces('application/json')]
    public function getAllUser(#[AcWebValueFromQuery('id')] int $id): void {
        echo json_encode([
            'message' => 'Users list',
            'data' => [$id]
        ]);
    }

    #[AcWebRoute(path: '/get/{id}', method: 'GET')]
    // #[Authorize(['admin'])]
    // #[Produces('application/json')]
    public function getUser(
        #[AcWebValueFromPath('id')] int $id
    ): void {
        echo json_encode([
            'message' => 'Users list',
            'data' => [
                'id'=> $id,
            ]
        ]);
    }

    #[AcWebRoute(path: '/update', method: 'POST')]
    public function updateUser(#[AcWebValueFromBody] User $user): void {
        print_r($user);
        echo json_encode([
            'message' => 'User updated',
            'data' => $user
        ]);
    }
}
