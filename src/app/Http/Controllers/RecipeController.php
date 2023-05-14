<?php

namespace App\Http\Controllers;

use App\Http\Requests\RecipeStoreRequest;
use App\Http\Requests\RecipeUpdateRequest;
use App\Models\URecipe;
use App\Models\URecipeImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

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
     * @param RecipeStoreRequest $request
     * @return JsonResponse
     */
    public function store(RecipeStoreRequest $request): JsonResponse
    {
        $inputParams = $request->validated();        // バリデーション済みの値を取得

        // 値をセットして保存
        $createdRecipe = DB::transaction(function () use ($inputParams) {
            $recipe = new URecipe;
            $recipe->fillParams($inputParams, Auth::id());
            $recipe->save();

            return URecipeImage::create(
                collect($inputParams['image_ids']), $recipe, Auth::id());
        });

        return response()->json(
            $createdRecipe->toArray(),
            self::STATUS_CODE_CREATED);
    }

    /**
     * レシピを更新
     * @param RecipeUpdateRequest $request
     * @param int $id レシピID
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(RecipeUpdateRequest $request, int $id): JsonResponse
    {
        $recipe = URecipe::findByIdAndUserId($id, Auth::id());
        if (is_null($recipe)) {
            return response()->json(
                ['messages' => 'Target recipe is not found.'],
                self::STATUS_CODE_NOT_FOUND);
        }

        $inputParams = $request->validated();        // バリデーション済みの値を取得
        $updatedRecipe = DB::transaction(function () use ($inputParams, $recipe) {
            // u_recipes の値を更新
            $recipe->fillParams($inputParams, null);
            $recipe->save();

            // u_recipe_images の値を更新
            return URecipeImage::deleteInsert(
                $recipe, $inputParams['image_ids'] ?? [], Auth::id());
        });

        return response()->json($updatedRecipe->toArray());
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
