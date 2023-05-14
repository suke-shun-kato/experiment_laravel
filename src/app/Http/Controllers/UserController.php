<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserSignUpRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * ユーザー登録処理
     * @param UserSignUpRequest $request ユーザー登録処理用のフォームリクエスト
     * @throws ValidationException
     */
    public function signUp(UserSignUpRequest $request): JsonResponse
    {
        // バリデーション済みの値を取得
        $validatedData = $request->validated();

        $accessToken = DB::transaction(function () use ($validatedData) {
            // ユーザーを登録
            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
            ]);

            // トークンを作成
            return $user->createToken('auth_token');
        });

        return response()->json([
            'access_token' => $accessToken->plainTextToken,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * ログイン処理
     * @return JsonResponse
     */
    public function login(UserLoginRequest $request): JsonResponse
    {
        // リクエストパラメータの email, passwordの値のみ抜き出す
        $params = $request->only('email', 'password');

        // 認証処理を実行
        if (!Auth::attempt($params)) {
            // 認証NGの場合
            return response()->json([
                'message' => 'The email or password is not correct.'
            ], self::STATUS_CODE_UNAUTHORIZED);
        }

        // Userモデルを取得
        $user = User::findByEmail($request['email']);

        // トークンを作成
        $accessToken = DB::transaction(function () use ($user) {
            // ここの戻り値が transaction() の戻り値になる
            return $user->createToken('auth_token');
        });

        return response()->json([
            'access_token' => $accessToken->plainTextToken,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * ログイン中のユーザー情報を取得する
     */
    public function getUser(Request $request)
    {
        return $request->user();
    }
}
