<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Repositories\User\UserRepositorylnterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;
use phpseclib3\Net\SSH2;

class UserController extends Controller
{
    private $repository;

    public function __construct(UserRepositorylnterface $repository)
    {
        $this->repository = $repository;
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->toArray(), [
            'email' => ['required', 'email', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            $data = ['message' => $validator->messages()];
            return ResponseBuilder::error(400, null, $data);
        }

        $user = $this->repository->getUserByEmail($request->email);

        if (!$user || !Hash::check($request->password, $user->password)) {
            $data = ['message' => __('The provided credentials are incorrect.')];
            return ResponseBuilder::error(401, null, $data);
        }

        $data = ['token' => $user->createToken('token_base_name')->plainTextToken];
        return ResponseBuilder::success($data, 200);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->toArray(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'string', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            $data = ['message' => $validator->messages()];
            return ResponseBuilder::error(400, null, $data);
        }

        $user = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password
        ];

        $user = $this->repository->create($user);
        $token = $user->createToken('auth_token')->plainTextToken;

        $data = [
            'access_token' => $token,
            'token_type' => 'Bearer',
        ];
        return ResponseBuilder::success($data, 200);
    }

    public function getRunningProcesses()
    {
        $processes = $this->runCommandOnServer('ps -aux');
        $data = ['process' => $processes];
        return ResponseBuilder::success($data, 200);
    }

    public function createNewDirectory(Request $request)
    {

        $validator = Validator::make($request->toArray(), [
            'name' => ['required', 'string', 'regex:/^[a-zA-Z0-9]*$/'],
        ]);

        if ($validator->fails()) {
            $data = ['message' => __('Folder name does not meet naming rules.')];
            return ResponseBuilder::error(400, null, $data);
        }

        $name = $request->name;
        $username = 'root';
        $bash = 'DIR="/opt/myprogram/' . $username . '/' . $name . '"
if [ -d $DIR ]
then
echo true
else
echo false
fi';
        $directoyExists = $this->runCommandOnServer($bash);

        if (trim($directoyExists) === "false") {
            $this->runCommandOnServer('mkdir -p /opt/myprogram/' . $username . '/' . $name . '/');
            $data = ['message' => __('Directory created successfully'), 'created' => true];
            return ResponseBuilder::success($data, 200);

        } else {
            $data = ['message' => __('Directory exists'), 'created' => false];
            return ResponseBuilder::error(200, null, $data);
        }

        return $name;
    }

    public function getListOfDirectories()
    {
        $username = 'root';
        $listOfDirectories = $this->runCommandOnServer('ls /opt/myprogram/' . $username);

        $listOfDirectories = preg_split('~\R~', $listOfDirectories);
        unset($listOfDirectories[count($listOfDirectories) - 1]);

        $mesage = empty($listOfDirectories) ? __('Directory is empty') : __('Directory listed successfully');
        $data = ['message' => $mesage];

        if (!empty($listOfDirectories)) {
            $data ['directories'] = $listOfDirectories;
        }

        return ResponseBuilder::success($data, 200);
    }

    public function getListOfFiles()
    {
        $username = 'root';
        $listOfFiles = $this->runCommandOnServer('ls -p /opt/myprogram/' . $username . ' | grep -v /');

        $listOfFiles = preg_split('~\R~', $listOfFiles);
        unset($listOfFiles[count($listOfFiles) - 1]);


        $mesage = empty($listOfFiles) ? __('No files Found') : __('Files listed successfully');
        $data = ['message' => $mesage];

        if (!empty($listOfFiles)) {
            $data ['files'] = $listOfFiles;
        }

        return ResponseBuilder::success($data, 200);
    }

    private function runCommandOnServer($command)
    {
        $host = '';
        $port = '22';
        $usr = 'root';
        $pass = '';

        $ssh = new SSH2($host, $port);
        $ssh->login($usr, $pass);
        return $ssh->exec($command);
    }
}
