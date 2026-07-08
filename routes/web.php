<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\IncomeEstimateController;
use App\Http\Controllers\IncomeEstimateDetailController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\ApprovalSettingController;
use App\Http\Controllers\FundApprovalController;
use App\Http\Controllers\FundRequestController;
use App\Http\Controllers\JournalEntryController;
use App\Http\Controllers\BudgetAllocationController;
use App\Http\Controllers\BudgetPeriodController;
use App\Http\Controllers\BudgetProgramController;
use App\Http\Controllers\BudgetProgramDetailController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\RolePermissionController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('login'));

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', fn() => view('dashboard'))->name('dashboard');
    Route::resource('organizations', OrganizationController::class)->except(['show']);
    Route::resource('departments', DepartmentController::class)->except(['show']);
    Route::resource('positions', PositionController::class)->except(['show']);
    Route::get('budget-periods/active-period', [BudgetPeriodController::class, 'activePeriod'])->name('budget-periods.active');
    Route::resource('budget-periods', BudgetPeriodController::class)->except(['show']);
    Route::resource('budget-allocations', BudgetAllocationController::class)->except(['show']);
    Route::get('budget-allocations-departments', [BudgetAllocationController::class, 'getDepartments'])->name('budget-allocations.departments');

    // Budget Programs & Details
    Route::resource('budget-programs', BudgetProgramController::class);
    Route::post('budget-program-details', [BudgetProgramDetailController::class, 'store'])->name('budget-program-details.store');
    Route::get('budget-program-details/{budgetProgramDetail}/edit', [BudgetProgramDetailController::class, 'edit'])->name('budget-program-details.edit');
    Route::put('budget-program-details/{budgetProgramDetail}', [BudgetProgramDetailController::class, 'update'])->name('budget-program-details.update');
    Route::delete('budget-program-details/{budgetProgramDetail}', [BudgetProgramDetailController::class, 'destroy'])->name('budget-program-details.destroy');
    Route::resource('employees', EmployeeController::class);
    Route::resource('users', UserController::class)->except(['show']);
    Route::get('role-permissions', [RolePermissionController::class, 'index'])->name('role-permissions.index')->middleware('permission:menu.role-permissions');
    Route::put('role-permissions/{role}', [RolePermissionController::class, 'update'])->name('role-permissions.update')->middleware('permission:menu.role-permissions');
    Route::resource('accounts', AccountController::class)->except(['show']);
    Route::get('accounts-parents', [AccountController::class, 'getParents'])->name('accounts.parents');
    Route::resource('journal-entries', JournalEntryController::class);
    Route::post('journal-entries/{journal_entry}/post', [JournalEntryController::class, 'post'])->name('journal-entries.post');
    Route::get('journal-accounts', [JournalEntryController::class, 'getAccounts'])->name('journal-entries.accounts');

    // Estimasi Pendapatan
    Route::resource('income-estimates', IncomeEstimateController::class);
    Route::resource('income-estimate-details', IncomeEstimateDetailController::class)
        ->only(['create', 'store', 'edit', 'update', 'destroy']);

    // Approval Settings
    Route::get('approval-settings/edit-chain', [ApprovalSettingController::class, 'editChain'])->name('approval-settings.edit-chain');
    Route::post('approval-settings/update-chain', [ApprovalSettingController::class, 'updateChain'])->name('approval-settings.update-chain');
    Route::resource('approval-settings', ApprovalSettingController::class)->only(['index', 'create', 'store', 'destroy']);

    // Pengajuan Dana
    Route::resource('fund-requests', FundRequestController::class);
    Route::post('fund-requests/{fund_request}/submit', [FundRequestController::class, 'submit'])->name('fund-requests.submit');
    Route::get('fund-requests-deps', [FundRequestController::class, 'getDependencies'])->name('fund-requests.deps');

    // Audit Log
    Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    Route::get('audit-logs/{auditLog}', [AuditLogController::class, 'show'])->name('audit-logs.show');

    // Inbox Approval
    Route::get('fund-approvals/inbox', [FundApprovalController::class, 'inbox'])->name('fund-approvals.inbox');
    Route::post('fund-approvals/{fundRequestApproval}/approve', [FundApprovalController::class, 'approve'])->name('fund-approvals.approve');
    Route::post('fund-approvals/{fundRequestApproval}/reject', [FundApprovalController::class, 'reject'])->name('fund-approvals.reject');
    Route::post('employees/{employee}/positions', [EmployeeController::class, 'assignPosition'])->name('employees.positions.assign');
    Route::delete('employees/{employee}/positions/{position}', [EmployeeController::class, 'removePosition'])->name('employees.positions.remove');
});
