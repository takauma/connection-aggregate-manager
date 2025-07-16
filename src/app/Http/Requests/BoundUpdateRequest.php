<?php

namespace App\Http\Requests;

use App\Constants\ResultCdConstants;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;

/**
 * ボンディング更新要求.
 * @author Soma Takahashi
 */
class BoundUpdateRequest extends FormRequest {
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
			"boundName" => "required|max:32",
			"vpsName" => "required|max:32|unique:bound_mst,vps_name," . $this["boundName"] . ",bound_name",
			"vpsKey" => "required|max:128|unique:bound_mst,vps_key," . $this["boundName"] . ",bound_name",
			"vpsHost" => "required|max:64|unique:bound_mst,vps_host," . $this["boundName"] . ",bound_name",
			"omrVersion" => "max:16",
			"configVersion" => "max:8",
			"omrRemoteUrl" => "nullable|url|max:64",
			"swRemoteUrl" => "nullable|url|max:64",
			"apRemoteUrl" => "nullable|url|max:64",
			"omrSshForwardPort" => "nullable|integer|between:1,65535",
			"router39_1Id" => "max:64",
			"router40_1Id" => "max:64",
			"router41_1Id" => "max:64",
			"router42_1Id" => "max:64",
			"router43_1Id" => "max:64",
			"router44_1Id" => "max:64",
			"router45_1Id" => "max:64"
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