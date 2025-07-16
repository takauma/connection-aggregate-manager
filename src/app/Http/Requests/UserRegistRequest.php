<?php

namespace App\Http\Requests;

use App\Constants\ResultCdConstants;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * ユーザー登録要求.
 * @author Soma Takahashi
 */
class UserRegistRequest extends FormRequest {
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
			"userId" => "required|max:64|regex:/^[A-Za-z0-9\!#-&\*\+\-\.:;\?_@\\\]+$/|unique:user_mst,user_id",
			"userName" => "required|max:32",
			"password" => "required|between:8,32|regex:/^[A-Za-z0-9\!#-&\*\+\-\.:;\?_@\\\]+$/",
			"passwordConfirm" => "required|same:password",
			"roleId" => "required|max:2147483647|exists:role_mst,role_id"
		];
	}

	/**
	 * 独自エラーメッセージを返却します.
	 * @return array 独自エラーメッセージ.
	 */
	public function messages(): array
	{
		$regexValidMsg = ":attributeに利用できない文字が含まれいます。";

		return [
			"userId.regex" => $regexValidMsg,
			"password.regex" => $regexValidMsg,
			"passwordConfirm.same" => "パスワードが一致しません。"
		];
	}

	/**
	 * バリデーション失敗時処理.
	 * @param Validator $validator バリデーター.
	 */
	protected function failedValidation(Validator $validator)
    {
		$response["resultCd"] = ResultCdConstants::PARAMERTER_ERROR;
        $response["errors"]  = $validator->errors()->toArray();

        throw new HttpResponseException(
            response()->json($response, 200)
		);
	}
}