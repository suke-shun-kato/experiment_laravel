<?php

namespace App\Http\Controllers;

use App\Models\URecipe;
use App\Models\URecipeImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $recipes = URecipe::getList(Auth::id());
        return response()->json(['recipes' => $recipes->toArray()]);
    }

    /**
     * レシピを1件取得
     * @param int $id レシピID
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $recipe = URecipe::findByIdAndUserId($id, Auth::id());

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
            'image_ids' => ['array'],
            'image_ids.*' => ['int'],
        ]);

        // 必須パラメータがない場合はエラー専用のメッセージを返す
        if ($validator->fails()) {
            return response()->json([
                'message' => "Recipe couldn't create",
                'required' => 'title, description'
            ], self::STATUS_CODE_BAD_REQUEST);

        }

        // 値をセットして保存
        $recipe = new URecipe;
        DB::transaction(function () use ($recipe, $validator) {
            $param = $validator->validated();

            $recipe->fillParams($param, Auth::id());
            $recipe->save();

            $recipe->images = URecipeImage::create($param['image_ids'], $recipe->id, Auth::id());
        });

        return response()->json(
            $recipe->toArray(),
            self::STATUS_CODE_CREATED);
    }

    /**
     * レシピを更新
     * @param Request $request
     * @param int $id レシピID
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
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

        $recipe = URecipe::findByIdAndUserId($id, Auth::id());
        if (is_null($recipe)) {
            return response()->json(
                ['messages' => 'Target recipe is not found.'],
                self::STATUS_CODE_NOT_FOUND);
        }

        DB::transaction(function () use ($validator, $recipe) {
            $recipe->fillParams($validator->validated(), null);
            $recipe->save();
        });

        return response()->json($recipe->toArray());
    }


    /**
     * 指定idのRecipeを削除
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $recipe = URecipe::findByIdAndUserId($id, Auth::id());
        if (is_null($recipe)) {
            return response()->json(
                ['messages' => 'Target recipe is not found.'],
                self::STATUS_CODE_NOT_FOUND);
        }

        // recipesテーブルから削除
        DB::transaction(function () use ($recipe) {
            $recipe->images()->delete();
            $recipe->delete();
        });

        return response()->json(
            [],
            self::STATUS_CODE_NOT_CONTENT);
    }
}
