<?php

namespace Modules\Ecommerce\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Customer;
use Modules\Ecommerce\Mail\Verify;
use Modules\Ecommerce\Mail\VerifyCode;
use App\Models\MailSetting;
use Mail;
use Session;

class AuthController extends Controller
{
    use \App\Traits\MailInfo;

    public function register()
    {
        return view('ecommerce::frontend.registration');
    }

    public function processRegisterCustomer(Request $request) 
    {
        $this->validate($request, [
            'email'       => 'required|unique:users,email',
            'password'    => 'required|min:6|confirmed'
        ]);

        try {

            $data =  [
                'name'        => trim(htmlspecialchars($request->input('name'))),
                'email'       => trim(htmlspecialchars($request->input('email'))),
                'password'    => trim(bcrypt($request->input('password'))),
                'phone'       => '',
                'role_id'     => 5,
                'is_active'   => 0,
                'is_deleted'  => 0
            ];

            $user = User::create($data);
            
            $id = $user->id;

            $data =  [
                'name'               => trim(htmlspecialchars($request->input('name'))),
                'email'              => trim(htmlspecialchars($request->input('email'))),
                'phone_number'       => '',
                'address'            => '',
                'city'               => '',
                'user_id'            => $id,
                'customer_group_id'  => 1,
                'is_active'          => 0,
            ];

            $customer = Customer::create($data);

            $mail_setting = MailSetting::latest()->first();
            $this->setMailInfo($mail_setting);
            Mail::to($request->input('email'))->send(new Verify($data));

            return redirect()->route('customer.login', ['verify' => 0]);

        } catch(Exception $e) {

            $this->setErrorMessage($e->getMessage());

            return redirect()->back();
        }

    }

    public function verify($id)
    {
        $user = User::find($id);
        $user->is_active = 1;
        $user->save();

        $customer = Customer::where('user_id',$id)->first();
        $customer->is_active = 1;
        $customer->save();

        return redirect()->route('customer.login', ['verify' => 1]);
    }

    public function login($verify='')
    {
        return view('ecommerce::frontend.login', compact('verify'));
    }

    public function processLogin(Request $request) 
    {


        if ($request->filled('phone')) {

            $request->validate([
                'phone' => 'required'
            ]);
            $user = User::where('phone', $request->phone)->first();     
            if ($user) {

                auth()->login($user); 
                $user = auth()->user();
                $customer = Customer::where('user_id', $user->id)->first(); 
                // return view('ecommerce::frontend.dashboard', compact('customer'));
                return redirect()->route('customer.profile'); 
                
            }else{
                Session::flash('message', 'Invalid Mobile Number');
                Session::flash('type', 'danger'); 
                return view('ecommerce::frontend.login');
            }

        }else{

            $this->validate($request, [
                'email'   => 'required',
                'password'=> 'required|min:6'

            ]);

            $credentials = $request->except(['_token','checkout']);

            if(auth()->attempt($credentials)) {
                
                $user = auth()->user();

                if(isset($request->checkout) && ($request->checkout == 1)){
                    return redirect()->back();
                }
                
                return redirect()->route('customer.profile'); 

            } else {
                Session::flash('message', 'Invalid email or password');
                Session::flash('type', 'danger'); 
        
                return view('ecommerce::frontend.login');
            }
        }

    }

    public function getEmail()
    {
        return view('ecommerce::frontend.forgot-password');
    }

    public function checkEmail(Request $request)
    {
        $email = $request->input('email');
        $user = User::where('email',$email)->first();
        if(!empty($user)) {
            $mail_setting = MailSetting::latest()->first();
            $this->setMailInfo($mail_setting);

            $data = [
                'code' => rand(100000, 999999),
                'id'   => $user->id
            ];

            Mail::to($email)->send(new VerifyCode($data));

            session(['verification_code' => $data['code']]);

            return view('ecommerce::frontend.verify-code',compact('user'));
        } else {
            Session::flash('message', 'This email is not in our system!');
            Session::flash('type', 'danger');
            
            return view('ecommerce::frontend.forgot-password'); 
        }
    }
    
    public function getPass(Request $request)
    {
        $code = session()->has('verification_code') ? session()->get('verification_code') : 0;
        if($code = $request->code){
            $id = $request->id;
            return view('ecommerce::frontend.change-password',compact('id'));
        } else {
            Session::flash('message', 'Sorry, verification code didn not match');
            Session::flash('type', 'danger');
            
            return view('ecommerce::frontend.forgot-password'); 
        }
    }
    
    public function changePass(Request $request)
    {
        $password = $request->password;
        $id = $request->id;
        
        $user = User::find($id);
        $user->password = trim(bcrypt($password));
        $user->save();
        
        Session::flash('message', 'Your password has been updated.');
        Session::flash('type', 'danger');
        
        return view('ecommerce::frontend.login'); 
    }

    public function logout(Request $request)
    {
        auth()->logout();
        return redirect('/customer/login');
    }
    //customer mobile otp login
    public function mobileLogin(Request $request){

        $request->validate([
            'phone' => 'required'
        ]);
        $user = User::where('phone', $request->phone)->first();     
        if ($user) {

            auth()->login($user); 
             $user = auth()->user();
            $customer = Customer::where('user_id', $user->id)->first(); dd($customer);
            return view('ecommerce::frontend.dashboard', compact('customer'));
           // return redirect()->route('customer.profile')->with('success', 'Logged in successfully!');
        }else{
             Session::flash('message', 'Invalid Mobile Number');
            Session::flash('type', 'danger'); 
            return view('ecommerce::frontend.login');
        }
    }
    
    //check mobile no exist status
    public function mobileExistStatus(Request $request){
        $request->validate([
            'phone' => 'required'
        ]);
        $user = User::where('phone', $request->phone)->first();    
        if($user){
            return response()->json([
                                        'status' => true,
                                        'message' => 'valid Mobile number',
                                    ]);

        }else{
            return response()->json([
                                        'status' => false,
                                        'message' => 'Invalid Mobile number',
                                    ]);
            
        }

    }
    //mobilesignup
    public function mobileSignUp(Request $request){
        try{
                $request->validate([
                    'guest_name'=>'required',
                    'guest_phone'=>'required'
                ],[
                    'guest_name.required'=>'The Name field is required',
                    'guest_phone.required'=>'The Mobile Number field is required',
                ]);

                $data =  [
                        'name'        => trim(htmlspecialchars($request->input('guest_name'))),
                        'email'       => "demo",
                        'password'    => trim(bcrypt('demo')),
                        'phone'       => $request->input('guest_phone'),
                        'role_id'     => 5,
                        'is_active'   => 0,
                        'is_deleted'  => 0
                    ];

                    $user = User::create($data);
                    
                    $id = $user->id;

                    $data =  [
                        'name'               => trim(htmlspecialchars($request->input('guest_name'))),
                        'email'              => "demo",
                        'phone_number'       => $request->input('guest_phone'),
                        'address'            => '',
                        'city'               => '',
                        'user_id'            => $id,
                        'customer_group_id'  => 1,
                        'is_active'          => 0,
                    ];

                    $customer = Customer::create($data);

                return redirect()->back()->with(['success' => 'User created successfully!']);
         } catch(Exception $e) {
            $this->setErrorMessage($e->getMessage());
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    
    public function getMobileLogin(){

        $user = auth()->user();
        $id = $user->id;
        $customer = Customer::where('user_id', $user->id)->first(); dd($customer);
        return view('ecommerce::frontend.dashboard', compact('customer'));
        
    }
}
