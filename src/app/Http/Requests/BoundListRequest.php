<?php

namespace App\Http\Requests;

use App\Constants\ResultCdConstants;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * ボンディング一覧取得要求.
 * @author Soma Takahashi
 */
class BoundListRequest extends FormRequest {
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
			"sortKey" => "bail|nullable|in:bound_name,vps_name,vps_host,omr_version,config_version,created_datetime,modified_datetime",
			"order" => "bail|nullable|in:ASC,DESC"
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