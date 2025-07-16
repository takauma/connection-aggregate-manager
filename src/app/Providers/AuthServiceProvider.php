<?php

namespace App\Providers;

use App\Constants\RoleConstants;
use App\Models\UserMst;
use App\Models\UserRole;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // アクセス制限ゲート: システム管理者のみ.
        Gate::define("onlySystemAdmin", function(UserMst $userMst): bool {
            return $this->getRoleId($userMst) === RoleConstants::ADMIN;
        });

        // アクセス制限ゲート: ボンディング管理者、またはシステム管理者のみ.
        Gate::define("onlyBoundAdminOrSystemAdmin", function(UserMst $userMst): bool {
            $roleId = $this->getRoleId($userMst);
            return $roleId === RoleConstants::BOUND_ADMIN || $roleId === RoleConstants::ADMIN;
        });
    }

    /**
     * 権限IDを取得します.
     * @param UserMst $userMst ユーザーマスタ.
     * @param ?string 権限ID.
     */
    private function getRoleId(UserMst $userMst): ?string {
        $row = UserRole::select("role_id")->where(["user_id" => $userMst["user_id"], "delete_flg" => "0"])->first();
        if ($row == null) {
            return null;
        }
        return $row["role_id"];
    }
}
