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

        $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => bcrypt($request->password),
        ]);
        Auth::login($user);
        session()->flash('success', '欢迎，您将在这里开启一段新的旅程~');
        return redirect()->route('users.show', [$user]);
}

    public function edit(User $user)
    {
    return view('users.edit', compact('user'));
    }

    public function update(User $user, Request $request)
    {
    $this->validate($request, [
    'name' => 'required|max:50',
    'password' => 'nullable|confirmed|min:6'
    ]);
    $data = [];
    $data['name'] = $request->name;
    if($request->password) {
        $data['password'] = bcrypt($request->password);
    }
    $user->update($data);
    
    session()->flash('success', '个人资料更新成功！');
    
    return redirect()->route('users.show', $user->id);
    }

}
