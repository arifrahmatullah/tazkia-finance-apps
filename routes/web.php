<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\ApprovalSettingController;
use App\Http\Controllers\FundApprovalController;
use App\Http\Controllers\FundRequestController;
use App\Http\Controllers\JournalEntryController;
use App\Http\Controllers\BudgetAllocationController;
use App\Http\Controllers\BudgetPeriodController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PositionController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('login'));

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', fn() => view('dashboard'))->name('dashboard');
    Route::resource('organizations', OrganizationController::class)->except(['show']);
    Route::resource('departments', DepartmentController::class)->except(['show']);
    Route::resource('positions', PositionController::class)->except(['show']);
    Route::resource('budget-periods', BudgetPeriodController::class)->except(['show']);
    Route::resource('budget-allocations', BudgetAllocationController::class)->except(['show']);
    Route::get('budget-allocations-departments', [BudgetAllocationController::class, 'getDepartments'])->name('budget-allocations.departments');
    Route::resource('employees', EmployeeController::class);
    Route::resource('users', UserController::class)->except(['show']);
    Route::resource('accounts', AccountController::class)->except(['show']);
    Route::get('accounts-parents', [AccountController::class, 'getParents'])->name('accounts.parents');
    Route::resource('journal-entries', JournalEntryController::class);
    Route::post('journal-entries/{journal_entry}/post', [JournalEntryController::class, 'post'])->name('journal-entries.post');
    Route::get('journal-accounts', [JournalEntryController::class, 'getAccounts'])->name('journal-entries.accounts');

    // Approval Settings
    Route::resource('approval-settings', ApprovalSettingController::class)->except(['show']);

    // Pengajuan Dana
    Route::resource('fund-requests', FundRequestController::class);
    Route::post('fund-requests/{fund_request}/submit', [FundRequestController::class, 'submit'])->name('fund-requests.submit');
    Route::get('fund-requests-deps', [FundRequestController::class, 'getDependencies'])->name('fund-requests.deps');

    // Inbox Approval
    Route::get('fund-approvals/inbox', [FundApprovalController::class, 'inbox'])->name('fund-approvals.inbox');
    Route::post('fund-approvals/{fundRequestApproval}/approve', [FundApprovalController::class, 'approve'])->name('fund-approvals.approve');
    Route::post('fund-approvals/{fundRequestApproval}/reject', [FundApprovalController::class, 'reject'])->name('fund-approvals.reject');
    Route::post('employees/{employee}/positions', [EmployeeController::class, 'assignPosition'])->name('employees.positions.assign');
    Route::delete('employees/{employee}/positions/{position}', [EmployeeController::class, 'removePosition'])->name('employees.positions.remove');
});
