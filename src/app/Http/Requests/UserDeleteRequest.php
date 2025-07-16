<?php

namespace App\Http\Requests;

use App\Constants\AppConstants;
use App\Constants\ResultCdConstants;
use App\Models\UserMst;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * ユーザー削除要求.
 * @author Soma Takahashi
 */
class UserDeleteRequest extends FormRequest {
	/**
	 * リクエストの認可有無を返却します.
	 * @return bool 認可有無.
	 */
	public function authorize(): bool
	{
		return true;
	}

	/**
	 * バリデーションルールを返却します.
	 * @return array バリデーションルール.
	 */
	public function rules(): array
	{
		return [
			"id" => "bail|required|integer|max:2147483647|regex:/^[A-Za-z0-9\!#-&\*\+\-\.:;\?@\\\]+$/|exists:user_mst,id",
			"userId" => "bail|required|max:64|exists:user_role,user_id"
		];
	}

	/**
	 * バリデーション失敗時処理.
	 * @param Validator $validator バリデーター.
	 */
	protected function failedValidation(Validator $validator)
    {
		$response["resultCd"] = ResultCdConstants::PARAMERTER_ERROR;

        throw new HttpResponseException(
            response()->json($response, 200)
		);
	}
}