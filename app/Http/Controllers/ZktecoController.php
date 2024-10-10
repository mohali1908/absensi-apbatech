<?php


namespace Laradevsbd\Zkteco\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;


use Laradevsbd\Zkteco\Http\Library\ZktecoLib;

     //    1 s't parameter int $uid Unique ID (max 65535)
    //    2 nd parameter int|string $userid ID in DB (same like $uid, max length = 9, only numbers - depends device setting)
    //    3 rd parameter string $name (max length = 24)
    //    4 th parameter int|string $password (max length = 8, only numbers - depends device setting)
    //    5 th parameter int $role Default Util::LEVEL_USER
    //    return bool|mixed

    /* The role of user. The length of $role is 1 byte. Possible value of $role are:
    0 = LEVEL_USER
    2 = LEVEL_ENROLLER
    12 = LEVEL_MANAGER
    14 = LEVEL_SUPERMANAGER */


    //setUser($uid, $userid, $name, $password, $role)
    
class ZktecoController extends Controller
{

    public function index()
    {
        $zk = new ZktecoLib(config('192.168.21.152'),config('4370'));
        if ($zk->connect()){
        $attendance = $zk->getAttendance();
        return view('zkteco::app',compact('attendance'));
        }
        
    }

    public function addUser()
    {
        $zk = new ZktecoLib(config('192.168.21.152'),config('4370'));
        if ($zk->connect()){
            $role = 0; //14= super admin, 0=User :: according to ZKtecho Machine
            $users = $zk->getUser();
            $total = end($users);
            $lastId=$total[3]+1;
            $zk->setUser('111111', '11', 'testuser', '234', $role);
            return "Add user success";
        }
        else{
            return "Device not connected";
        }
    } 
}