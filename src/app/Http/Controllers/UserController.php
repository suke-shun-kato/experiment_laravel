<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * ユーザー登録処理
     * @throws ValidationException
     */
    public function register(Request $request): JsonResponse
    {
        // $request->validate() でバリデーションをすると、
        $validator = Validator::make($request->input(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
//            'password' => 'required|string|min:8',
            'password' => 'required|string',
        ]);

        // 必須パラメータがない場合はエラー専用のメッセージを返す
        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()
            ], self::STATUS_CODE_BAD_REQUEST);
        }
        // バリデーション済みの値を取得
        $validatedData = $validator->validated();

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
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
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
