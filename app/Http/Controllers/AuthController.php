<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function signIn(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'email' => 'required|email:rfc,dns',
                'password' => 'required|min:4',
            ],
            [
                'required'  => ':attribute harus diisi',
                'min'       => ':attribute minimal :min karakter',
                'email'     => ':attribute harus berisi email yang valid',
            ]
        );

        try {
            $code = 200;

            if ($validator->fails()) {
                throw new \Exception($validator->errors()->first(), 422);
            }

            if (!$token = Auth::claims(['foo' => 'bar'])->attempt($request->only('email', 'password'))) {
                throw new \Exception('Email Atau Password Tidak Sesuai', 401);
            }

            $payload = Auth::payload(); // atau bisa menggunakan helper auth()
            // $payload = auth()->payload();

            $res = [
                'response' => [
                    'token' => $token,
                    'payload' => $payload,
                ],
                'metadata' => [
                    'message' => 'OK',
                    'code'    => 200
                ]
            ];
        } catch (TokenExpiredException $e) {
            throw new \Exception('Token expired', $e->getCode());
        } catch (TokenInvalidException $e) {
            throw new \Exception('Token invalid', $e->getCode());
        } catch (JWTException $e) {
            throw new \Exception('Token absent: ' . $e->getMessage(), $e->getCode());
        } catch (Exception $e) {
            $code = $e->getCode();
            $res = [
                'metadata' => [
                    'message' => $e->getMessage(),
                    'code'    => $e->getCode()
                ]
            ];
        }
        return response()->json($res, $code);
    }

    public function signUp(Request $request)
    {
        //set validation
        $validator = Validator::make(
            $request->all(),
            [
                'name'      => 'required',
                'email'     => 'required|email|unique:users', // unique in table users
                'password'  => 'required|min:5|confirmed' // confirmed with same input
            ],
            [
                'required'  => ':attribute harus diisi',
                'min'       => ':attribute minimal :min karakter',
                'email'     => ':attribute harus berisi email yang valid',
                'unique'    => ":attribute {$request->email} sudah digunakan",
                'confirmed' => ':attribute konfirmasi harus sesuai'
            ]
        );

        //if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //create user
        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password)
        ]);

        //return response JSON user is created
        if ($user) {
            return response()->json([
                'success' => true,
                'user'    => $user,
            ], 201);
        }

        //return JSON process insert failed 
        return response()->json([
            'success' => false,
        ], 409);
    }

    public function signOut()
    {
        //remove token
        $removeToken = JWTAuth::invalidate(JWTAuth::getToken());

        if ($removeToken) {
            //return response JSON
            return response()->json([
                'success' => true,
                'message' => 'Logout Berhasil!',
            ]);
        }
    }
}
