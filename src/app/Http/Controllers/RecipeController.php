<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RecipeController extends Controller
{


    /**
     * レシピ一覧を取得
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $recipes = Recipe::all();
        return response()->json(['recipes' => $recipes->toArray()]);
    }

    /**
     * レシピを1件取得
     * @param int $id レシピID
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        /** @var Recipe $recipe */
        $recipe = Recipe::find($id);

        // エラー処理
        if (is_null($recipe)) {
            return response()->json([
                'messages' => 'Target recipe is not found.'
            ], self::STATUS_CODE_NOT_FOUND);
        }

        return  response()->json($recipe->toArray());
    }


    /**
     * レシピを新規登録
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // バリデータを作成
        $validator = Validator::make($request->input(), [
            'title' => ['required', 'string'],
            'description' => ['required', 'string'],
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
        DB::transaction(function () use ($request, $recipe) {
            $recipe->setRequestParamIfExists($request, true);
            $recipe->save();
        });

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

        // バリデータを作成
        $validator = Validator::make($request->input(), [
            'title' => ['string'],
            'description' => ['string'],
        ]);

        // 必須パラメータがない場合はエラー専用のメッセージを返す
        if ($validator->fails()) {
            return response()->json([
                'message' => "Recipe couldn't update",
                'required' => ''
            ], self::STATUS_CODE_BAD_REQUEST);

        }

        /** @var Recipe $recipe */
        $recipe = Recipe::find($id);
        if (is_null($recipe)) {
            return response()->json(
                ['messages' => 'Target recipe is not found.'],
                self::STATUS_CODE_NOT_FOUND);
        }

        DB::transaction(function () use ($request, $recipe) {
            $recipe->setRequestParamIfExists($request, false);
            $recipe->save();
        });

        return response()->json($recipe->toArray());
    }


    /**
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        /** @var Recipe $recipe */
        $recipe = Recipe::find($id);
        if (is_null($recipe)) {
            return response()->json(
                ['messages' => 'Target recipe is not found.'],
                self::STATUS_CODE_NOT_FOUND);
        }

        // recipesテーブルから削除
        DB::transaction(function () use ($recipe) {
            $recipe->delete();
        });

        return response()->json(
            [],
            self::STATUS_CODE_NOT_CONTENT);
    }

    private function validatea() {

    }
}
