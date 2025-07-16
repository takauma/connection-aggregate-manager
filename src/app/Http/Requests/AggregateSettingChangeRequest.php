<?php

namespace App\Http\Requests;

use App\Constants\ResultCdConstants;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * ボンディング集計設定変更要求.
 * @author Soma Takahashi
 */
class AggregateSettingChangeRequest extends FormRequest {
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
			"*" => "bail|required|array",
			"*.boundName" => "bail|required|max:32|exists:bound_mst,bound_name",
			"*.aggregateFlg" => "bail|required|between:0,1"
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