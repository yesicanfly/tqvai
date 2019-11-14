<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
class UsersController extends Controller
{
    //
    public function create()
    {
        return view('users.create');
    }

    public function show(User $user)
    {
    return view('users.show', compact('user')); //将用户数据与视图进行绑定
    }

    public function store(Request $request)    //依赖注入
        {
            $this->validate($request, [
                'name' => 'required|max:50', //不能为空  最大长度
                'email' => 'required|email|unique:users|max:255', // 不能为空  格式为email  唯一性验证
                'password' => 'required|confirmed|min:6' //不能为空  密码匹配一致性  最小值
            ]);
            return;
        }
}
