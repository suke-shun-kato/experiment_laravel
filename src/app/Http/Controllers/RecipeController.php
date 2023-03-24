<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RecipeController extends Controller
{
    const STATUS_CODE_CREATED = 201;
    const STATUS_CODE_NOT_CONTENT = 204;
    const STATUS_CODE_NOT_FOUND = 404;
    const STATUS_CODE_BAD_REQUEST = 400;

    /**
     * レシピ一覧を取得
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $recepes = Recipe::all();
        return response()->json(['recipes' => $recepes->toArray()]);
    }

    /**
     * レシピを1件取得
     * @param int $id レシピID
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $recepe = Recipe::find($id);

        // エラー処理
        if (is_null($recepe)) {
            return response()->json([
                'messages' => 'Target recipe is not found.'
            ], self::STATUS_CODE_NOT_FOUND);
        }

        return  response()->json($recepe->toArray());
    }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // バリデータを作成
        $validator = Validator::make($request->all(), [
            "title" => ["required"],
            "description" => ["required"],
        ]);

        // 必須パラメータがない場合はエラー専用のメッセージを返す
        if ($validator->fails()) {
            return response()->json([
                'message' => "Recipe couldn't create",
                'required' => 'title, description'
            ], self::STATUS_CODE_BAD_REQUEST);

        }

        // 値をセットして保存
        $recipe = new Recipe;
        $recipe->setRequestParamIfExists($request);
        $recipe->save();

        return response()->json(
            $recipe->toArray(),
            self::STATUS_CODE_CREATED);
    }

    /**
     * レシピを更新
     * @param int $id レシピID
     * @param Request $request
     * @return JsonResponse
     */
    public function update(int $id, Request $request): JsonResponse
    {
        $recipe = Recipe::find($id);
        if (is_null($recipe)) {
            return response()->json(
                ['messages' => 'Target recipe is not found.'],
                self::STATUS_CODE_NOT_FOUND);
        }

        $recipe->setRequestParamIfExists($request);
        $recipe->save();

        return response()->json($recipe->toArray());
    }


    /**
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $recipe = Recipe::find($id);
        if (is_null($recipe)) {
            return response()->json(
                ['messages' => 'Target recipe is not found.'],
                self::STATUS_CODE_NOT_FOUND);
        }

        // recipesテーブルから削除
        $recipe->delete();

        return response()->json(
            [],
            self::STATUS_CODE_NOT_CONTENT);
    }
}
