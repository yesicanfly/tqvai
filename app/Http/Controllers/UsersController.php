<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Auth;
use Mail;
class UsersController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth',[
            'except' => ['show','create','store','index', 'confirmEmail'] //除了指定动作外,所有其他动作都必须登录用户才能访问
        ]);
    }

    public function index()
        {
            $users = User::paginate(10); // 显示10页
            return view('users.index', compact('users'));
        }

    public function create()
    {
        return view('users.create');
    }

    public function show(User $user)
    {
    $statuses = $user->statuses()->orderBy('created_at', 'desc')->paginate(10);
    return view('users.show', compact('user', 'statuses'));// 将用户数据和微博动态数据同时传递给用户个人页面的视图上
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

        $this->sendEmailConfirmationTo($user);
        session()->flash('success', '验证邮件已发送到你的注册邮箱上，请注意查收。');
        return redirect('/');
}
    protected function sendEmailConfirmationTo($user)
        {
            $view = 'emails.confirm';
            $data = compact('user');
            $to = $user->email;
            $subject = "感谢注册 Weibo 应用！请确认你的邮箱。";

            Mail::send($view, $data, function ($message) use ($to, $subject) {
            $message->to($to)->subject($subject);
            });
        }

    public function edit(User $user)
    {
        $this->authorize('update', $user);
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

	public function destroy(User $user)
	{
    $this->authorize('destroy', $user);
	$user->delete();
	session()->flash('success', '成功删除用户！');
	return back();
	}

    public function confirmEmail($token)
    {
    $user = User::where('activation_token', $token)->firstOrFail();
    $user->activated = true;
    $user->activation_token = null;
    $user->save();
    Auth::login($user);
    session()->flash('success', '恭喜你，激活成功！');
    return redirect()->route('users.show', [$user]);
    }

}
