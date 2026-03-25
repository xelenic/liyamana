<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DesignController;
use App\Http\Controllers\DesignerApplicationController;
use App\Http\Controllers\FlipBookController;
use App\Http\Controllers\PageController;
use App\Models\Testimonial;
use Illuminate\Support\Facades\Route;

// Public routes (guests only - logged users are redirected to app)
Route::middleware('redirect.authenticated')->group(function () {
    Route::get('/', function () {
        $featuredTemplates = \App\Models\Template::query()
            ->where('is_active', true)
            ->where('is_public', true)
            ->featured()
            ->with('creator')
            ->limit(8)
            ->get();

        return view('home', [
            'testimonials' => Testimonial::active()->ordered()->get(),
            'featuredTemplates' => $featuredTemplates,
        ]);
    })->name('home');

    Route::get('/services', function () {
        return view('services');
    })->name('services');

    Route::get('/templates', [PageController::class, 'templates'])->name('templates');

    Route::get('/about', function () {
        return view('about');
    })->name('about');

    Route::get('/contact', function () {
        return view('contact');
    })->name('contact');
});

// Public documentation (no auth required)
Route::get('/docs', [\App\Http\Controllers\DocumentationController::class, 'index'])->name('docs.index');
Route::get('/docs/{slug}', [\App\Http\Controllers\DocumentationController::class, 'show'])->name('docs.show');

// Public flipbook view (no auth required)
Route::get('/flipbook/{slug}', [FlipBookController::class, 'publicView'])->name('flipbooks.public');

// Designer application (requires auth - user must be logged in to apply)
Route::middleware('auth')->group(function () {
    Route::get('/become-a-designer', [DesignerApplicationController::class, 'index'])->name('designer-application.index');
    Route::post('/become-a-designer', [DesignerApplicationController::class, 'store'])->name('designer-application.store');
    Route::post('/become-a-designer/generate-experience', [DesignerApplicationController::class, 'generateExperience'])->name('designer-application.generateExperience');
});

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
    Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google.redirect');
    Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
});

// Protected routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // AI send mail agent (prompt → compose and send email)
    Route::post('/ai/send-mail', [\App\Http\Controllers\AiSendMailController::class])->name('ai.send-mail');

    // User notifications (top bar dropdown)
    Route::get('/user/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('user.notifications');
    Route::post('/user/notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllRead'])->name('user.notifications.read-all');
    Route::post('/user/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markRead'])->name('user.notifications.read');

    // User settings (profile + password)
    Route::get('/user/settings', [\App\Http\Controllers\UserController::class, 'settings'])->name('user.settings');
    Route::put('/user/settings', [\App\Http\Controllers\UserController::class, 'updateSettings'])->name('user.settings.update');
    Route::post('/user/required-contact', [\App\Http\Controllers\UserController::class, 'saveRequiredContact'])->name('user.required-contact');

    // User address book
    Route::get('/user/address-book', [\App\Http\Controllers\UserController::class, 'addressBook'])->name('user.address-book');
    Route::post('/user/address-book', [\App\Http\Controllers\UserController::class, 'addressBookStore'])->name('user.address-book.store');
    Route::put('/user/address-book/{id}', [\App\Http\Controllers\UserController::class, 'addressBookUpdate'])->name('user.address-book.update');
    Route::delete('/user/address-book/{id}', [\App\Http\Controllers\UserController::class, 'addressBookDestroy'])->name('user.address-book.destroy');
    Route::get('/user/address-book/export-csv', [\App\Http\Controllers\UserController::class, 'addressBookExportCsv'])->name('user.address-book.export-csv');
    Route::get('/user/address-book/import-google', [\App\Http\Controllers\UserController::class, 'redirectToGoogleContacts'])->name('user.address-book.import-google');

    // rrweb session recording (user panel → admin replay)
    Route::post('/user/session-recording/start', [\App\Http\Controllers\UserSessionRecordingController::class, 'start'])
        ->middleware('throttle:40,1')
        ->name('user.session-recording.start');
    Route::post('/user/session-recording/append', [\App\Http\Controllers\UserSessionRecordingController::class, 'append'])
        ->middleware('throttle:300,1')
        ->name('user.session-recording.append');
    Route::post('/user/session-recording/finish', [\App\Http\Controllers\UserSessionRecordingController::class, 'finish'])
        ->middleware('throttle:80,1')
        ->name('user.session-recording.finish');

    Route::post('/user/heatmap/clicks', [\App\Http\Controllers\UserHeatmapController::class, 'ingest'])
        ->middleware('throttle:120,1')
        ->name('user.heatmap.ingest');

    Route::get('/enterprise', [\App\Http\Controllers\EnterpriseController::class, 'dashboard'])->name('enterprise');
    Route::get('/enterprise/mailbox', [\App\Http\Controllers\EnterpriseController::class, 'mailbox'])->name('enterprise.mailbox');
    Route::get('/enterprise/address-book', [\App\Http\Controllers\EnterpriseController::class, 'addressBook'])->name('enterprise.address-book');
    Route::post('/enterprise/address-book', [\App\Http\Controllers\EnterpriseController::class, 'addressBookStore'])->name('enterprise.address-book.store');
    Route::put('/enterprise/address-book/{id}', [\App\Http\Controllers\EnterpriseController::class, 'addressBookUpdate'])->name('enterprise.address-book.update');
    Route::delete('/enterprise/address-book/{id}', [\App\Http\Controllers\EnterpriseController::class, 'addressBookDestroy'])->name('enterprise.address-book.destroy');
    Route::get('/enterprise/address-book/export-csv', [\App\Http\Controllers\EnterpriseController::class, 'addressBookExportCsv'])->name('enterprise.address-book.export-csv');
    Route::post('/enterprise/address-book/import-csv', [\App\Http\Controllers\EnterpriseController::class, 'addressBookImportCsv'])->name('enterprise.address-book.import-csv');
    Route::get('/enterprise/schedule-mail', [\App\Http\Controllers\EnterpriseController::class, 'scheduleMail'])->name('enterprise.schedule-mail');
    Route::post('/enterprise/schedule-mail', [\App\Http\Controllers\EnterpriseController::class, 'scheduleMailStore'])->name('enterprise.schedule-mail.store');
    Route::delete('/enterprise/schedule-mail/{id}', [\App\Http\Controllers\EnterpriseController::class, 'scheduleMailDestroy'])->name('enterprise.schedule-mail.destroy');

    // My Orders
    Route::get('/orders', [\App\Http\Controllers\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{id}/invoice', [\App\Http\Controllers\OrderController::class, 'invoice'])->name('orders.invoice');
    Route::get('/orders/{id}', [\App\Http\Controllers\OrderController::class, 'show'])->name('orders.show');

    // Credits (Top Up)
    Route::get('/credits', [\App\Http\Controllers\CreditController::class, 'index'])->name('credits.index');
    Route::post('/credits/create-intent', [\App\Http\Controllers\CreditController::class, 'createTopupIntent'])->name('credits.createIntent');
    Route::post('/credits/process', [\App\Http\Controllers\CreditController::class, 'processTopup'])->name('credits.process');
    Route::post('/credits/payhere/initiate', [\App\Http\Controllers\CreditController::class, 'initiatePayHereTopup'])->name('credits.payhere.initiate');
    Route::get('/credits/payhere/return', [\App\Http\Controllers\CreditController::class, 'payHereReturn'])->name('credits.payhere.return');
    Route::get('/credits/payhere/cancel', [\App\Http\Controllers\CreditController::class, 'payHereCancel'])->name('credits.payhere.cancel');

    // Design Tool routes (Canva-like standalone tool)
    Route::prefix('design')->name('design.')->group(function () {
        Route::get('/', [DesignController::class, 'index'])->name('index');
        Route::get('/create', [DesignController::class, 'create'])->name('create');
        Route::post('/save', [DesignController::class, 'store'])->name('store');
        Route::post('/export', [DesignController::class, 'export'])->name('export');
        Route::get('/designs', [DesignController::class, 'designs'])->name('designs');
        Route::patch('/designs/{id}/name', [DesignController::class, 'updateDesignName'])->name('designs.updateName');
        Route::get('/show/{id}', [DesignController::class, 'show'])->name('show');
        Route::get('/letter/{id}/send', [DesignController::class, 'prepareSendLetter'])->name('letter.send');
        Route::post('/letter/prepare-from-editor', [DesignController::class, 'prepareSendLetterFromEditor'])->name('letter.prepareFromEditor');
        Route::get('/{id}/load', [DesignController::class, 'load'])->name('load');
        Route::delete('/{id}', [DesignController::class, 'destroy'])->name('destroy');

        // Image Library routes
        Route::prefix('image-library')->name('imageLibrary.')->group(function () {
            Route::get('/', [DesignController::class, 'imageLibraryIndex'])->name('index');
            Route::post('/upload', [DesignController::class, 'imageLibraryUpload'])->name('upload');
            Route::post('/delete', [DesignController::class, 'imageLibraryDelete'])->name('delete');
        });

        // Global Image Library (image parts) - shared across all users
        Route::get('/global-image-library', [DesignController::class, 'globalImageLibraryIndex'])->name('globalImageLibrary.index');

        // Font Library routes
        Route::prefix('font-library')->name('fontLibrary.')->group(function () {
            Route::get('/', [DesignController::class, 'fontLibraryIndex'])->name('index');
            Route::post('/upload', [DesignController::class, 'fontLibraryUpload'])->name('upload');
            Route::post('/delete', [DesignController::class, 'fontLibraryDelete'])->name('delete');
        });

        // Template routes
        Route::prefix('templates')->name('templates.')->group(function () {
            Route::get('/page', [DesignController::class, 'templatesPage'])->name('page');
            Route::get('/explore', [DesignController::class, 'exploreTemplates'])->name('explore');
            Route::get('/licenses', [DesignController::class, 'licensesIndex'])->name('licenses');
            Route::get('/categories', [DesignController::class, 'templateCategoriesIndex'])->name('categories');
            Route::post('/generate-descriptions', [DesignController::class, 'generateTemplateDescriptions'])->name('generateDescriptions');
            Route::get('/', [DesignController::class, 'templatesIndex'])->name('index');
            Route::post('/', [DesignController::class, 'templatesStore'])->name('store');
            Route::get('/{id}/manage', [DesignController::class, 'templatesManage'])->name('manage');
            Route::post('/{id}/products', [DesignController::class, 'templatesAssignProduct'])->name('products.assign');
            Route::delete('/{id}/products/{productId}', [DesignController::class, 'templatesUnassignProduct'])->name('products.unassign');
            Route::get('/{id}', [DesignController::class, 'templatesShow'])->name('show');
            Route::delete('/{id}', [DesignController::class, 'templatesDestroy'])->name('destroy');
            Route::get('/{id}/quick-use', [DesignController::class, 'quickUse'])->name('quickUse');
            Route::get('/{id}/send-letter', [DesignController::class, 'sendLetterCheckout'])->name('sendLetter');
        });

        Route::post('/special-offers-modal-dismiss', [DesignController::class, 'dismissSpecialOffersModal'])->name('specialOffersModal.dismiss');
        Route::post('/intro/mark-seen', [DesignController::class, 'markIntroTourSeen'])->name('intro.markSeen');

        // Checkout routes (quick use flow)
        Route::post('/checkout/init', [DesignController::class, 'checkoutInit'])->name('checkout.init');
        Route::get('/checkout/payment-options', [DesignController::class, 'paymentOptions'])->name('checkout.paymentOptions');
        Route::get('/checkout/payment', [DesignController::class, 'paymentGateway'])->name('checkout.payment');
        Route::post('/checkout/create-payment-intent', [DesignController::class, 'createPaymentIntent'])->name('checkout.createPaymentIntent');
        Route::post('/checkout/payment', [DesignController::class, 'processPayment'])->name('checkout.processPayment');

        // Post-order: template review & platform testimonial
        Route::post('/review-template', [DesignController::class, 'submitTemplateReview'])->name('review.submitTemplate');
        Route::post('/testimonial', [DesignController::class, 'submitTestimonial'])->name('testimonial.submit');

        // AI Content Templates (for design/templates/explore pages)
        Route::get('/ai-content-templates/pending', [DesignController::class, 'aiContentTemplatePending'])->name('aiContentTemplates.pending');
        Route::get('/ai-content-generations/{aiContentGeneration}/open', [DesignController::class, 'openAiContentGeneration'])->name('aiContentGenerations.open');
        Route::get('/ai-content-templates/result/{token}', [DesignController::class, 'aiContentTemplateResult'])->name('aiContentTemplates.result');
        Route::get('/ai-content-templates/{id}', [DesignController::class, 'aiContentTemplateShow'])->name('aiContentTemplates.show');
        Route::get('/ai-content-templates/{id}/form', [DesignController::class, 'aiContentTemplateForm'])->name('aiContentTemplates.form');
        Route::post('/ai-content-templates/{id}/generate', [DesignController::class, 'generateAiContentFromTemplate'])->name('aiContentTemplates.generate');

        // AI Design Generation route
        Route::post('/generate-ai', [DesignController::class, 'generateAIDesign'])->name('generateAI');
        Route::post('/generate-text-ai', [DesignController::class, 'generateTextContent'])->name('generateTextAI');
        Route::post('/generate-letter', [DesignController::class, 'generateLetter'])->name('generateLetter');
    });

    // Flip Book routes
    Route::prefix('flipbooks')->name('flipbooks.')->group(function () {
        Route::get('/', [FlipBookController::class, 'index'])->name('index');
        Route::get('/create', [FlipBookController::class, 'create'])->name('create');
        Route::get('/wizard', [FlipBookController::class, 'wizard'])->name('wizard');
        Route::post('/generate-description', [FlipBookController::class, 'generateDescription'])->name('generateDescription');
        Route::post('/create-from-design', [FlipBookController::class, 'createFromDesign'])->name('createFromDesign');
        Route::put('/{id}/update-from-design', [FlipBookController::class, 'updateFromDesign'])->name('updateFromDesign');
        Route::post('/wizard/step1', [FlipBookController::class, 'storeStep1'])->name('wizard.step1');
        Route::post('/wizard/step2', [FlipBookController::class, 'storeStep2'])->name('wizard.step2');
        Route::post('/wizard/step3', [FlipBookController::class, 'storeStep3'])->name('wizard.step3');
        Route::post('/wizard/step4', [FlipBookController::class, 'storeStep4'])->name('wizard.step4');
        Route::post('/wizard/complete', [FlipBookController::class, 'complete'])->name('wizard.complete');
        Route::post('/wizard/clear', [FlipBookController::class, 'clearWizard'])->name('wizard.clear');
        Route::get('/{id}/design', [FlipBookController::class, 'design'])->name('design');
        Route::post('/{id}/design', [FlipBookController::class, 'updateDesign'])->name('design.update');
        Route::get('/{id}/load-design', [FlipBookController::class, 'loadDesignForEdit'])->name('loadDesignForEdit');
        Route::get('/{id}/preview', [FlipBookController::class, 'preview'])->name('preview');
        Route::patch('/{id}', [FlipBookController::class, 'updateBasicInfo'])->name('update');
        Route::delete('/{id}', [FlipBookController::class, 'destroy'])->name('destroy');
        Route::get('/{id}', [FlipBookController::class, 'show'])->name('show');
    });

    // Admin routes
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [\App\Http\Controllers\AdminController::class, 'index'])->name('dashboard');
        Route::get('/reports', [\App\Http\Controllers\AdminReportsController::class, 'index'])->name('reports.index');
        Route::get('/reports/orders', [\App\Http\Controllers\AdminReportsController::class, 'orders'])->name('reports.orders');
        Route::get('/reports/credits', [\App\Http\Controllers\AdminReportsController::class, 'credits'])->name('reports.credits');
        Route::get('/reports/activity', [\App\Http\Controllers\AdminReportsController::class, 'activity'])->name('reports.activity');
        Route::get('/users', [\App\Http\Controllers\AdminController::class, 'users'])->name('users');
        Route::get('/users/{id}', [\App\Http\Controllers\AdminController::class, 'showUser'])->name('users.show');
        Route::get('/users/{id}/edit', [\App\Http\Controllers\AdminController::class, 'editUser'])->name('users.edit');
        Route::put('/users/{id}', [\App\Http\Controllers\AdminController::class, 'updateUser'])->name('users.update');
        Route::get('/users/{id}/login-as', [\App\Http\Controllers\AdminController::class, 'loginAsUser'])->name('users.login-as');
        Route::delete('/users/{id}', [\App\Http\Controllers\AdminController::class, 'deleteUser'])->name('users.delete');
        Route::get('/flipbooks', [\App\Http\Controllers\AdminController::class, 'flipbooks'])->name('flipbooks');
        Route::delete('/flipbooks/{id}', [\App\Http\Controllers\AdminController::class, 'deleteFlipbook'])->name('flipbooks.delete');
        Route::get('/templates', [\App\Http\Controllers\AdminController::class, 'templates'])->name('templates');
        Route::get('/templates/{id}/manage', [\App\Http\Controllers\AdminController::class, 'templateManage'])->name('templates.manage');
        Route::post('/templates/{id}/generate-thumbnail', [\App\Http\Controllers\AdminController::class, 'generateTemplateThumbnail'])->name('templates.generate-thumbnail');
        Route::delete('/templates/{id}', [\App\Http\Controllers\AdminController::class, 'deleteTemplate'])->name('templates.delete');
        Route::get('/designer-applications', [\App\Http\Controllers\AdminController::class, 'designerApplications'])->name('designer-applications');
        Route::get('/designer-applications/{id}', [\App\Http\Controllers\AdminController::class, 'showDesignerApplication'])->name('designer-applications.show');
        Route::post('/designer-applications/{id}/approve', [\App\Http\Controllers\AdminController::class, 'approveDesignerApplication'])->name('designer-applications.approve');
        Route::post('/designer-applications/{id}/reject', [\App\Http\Controllers\AdminController::class, 'rejectDesignerApplication'])->name('designer-applications.reject');
        Route::get('/orders', [\App\Http\Controllers\AdminController::class, 'orders'])->name('orders');
        Route::get('/orders/{id}', [\App\Http\Controllers\AdminController::class, 'showOrder'])->name('orders.show');
        Route::patch('/orders/{id}/delivery-status', [\App\Http\Controllers\AdminController::class, 'updateOrderDeliveryStatus'])->name('orders.updateDeliveryStatus');
        Route::get('/orders/{id}/pdf', [\App\Http\Controllers\AdminController::class, 'exportOrderPdf'])->name('orders.pdf');
        Route::get('/orders/{id}/pdf/{item}', [\App\Http\Controllers\AdminController::class, 'exportOrderItemPdf'])->name('orders.pdf.item');
        Route::get('/orders/{id}/preview', [\App\Http\Controllers\AdminController::class, 'previewOrderDesign'])->name('orders.preview');

        // Settings
        Route::get('/logs', [\App\Http\Controllers\AdminLogViewerController::class, 'index'])->name('logs');
        Route::get('/logs/view', [\App\Http\Controllers\AdminLogViewerController::class, 'show'])->name('logs.show');
        Route::post('/logs/clear', [\App\Http\Controllers\AdminLogViewerController::class, 'clear'])->name('logs.clear');

        Route::get('/heatmap', [\App\Http\Controllers\AdminUserHeatmapController::class, 'index'])->name('heatmap.index');
        Route::get('/heatmap/users/{user}', [\App\Http\Controllers\AdminUserHeatmapController::class, 'user'])->name('heatmap.user');
        Route::get('/heatmap/users/{user}/data', [\App\Http\Controllers\AdminUserHeatmapController::class, 'data'])->name('heatmap.data');

        Route::get('/session-recordings', [\App\Http\Controllers\AdminSessionRecordingController::class, 'index'])->name('session-recordings.index');
        Route::get('/session-recordings/users/{user}', [\App\Http\Controllers\AdminSessionRecordingController::class, 'userRecordings'])->name('session-recordings.user');
        Route::get('/session-recordings/{uuid}/replay', [\App\Http\Controllers\AdminSessionRecordingController::class, 'replay'])->whereUuid('uuid')->name('session-recordings.replay');
        Route::get('/session-recordings/{uuid}/events', [\App\Http\Controllers\AdminSessionRecordingController::class, 'events'])->whereUuid('uuid')->name('session-recordings.events');
        Route::delete('/session-recordings/{uuid}', [\App\Http\Controllers\AdminSessionRecordingController::class, 'destroy'])->whereUuid('uuid')->name('session-recordings.destroy');

        Route::get('/settings', [\App\Http\Controllers\AdminController::class, 'settings'])->name('settings');
        Route::post('/settings', [\App\Http\Controllers\AdminController::class, 'updateSettings'])->name('settings.update');
        Route::get('/settings/editor', [\App\Http\Controllers\AdminController::class, 'editorSettings'])->name('settings.editor');
        Route::post('/settings/editor', [\App\Http\Controllers\AdminController::class, 'updateEditorSettings'])->name('settings.editor.update');
        Route::get('/settings/payment', [\App\Http\Controllers\AdminController::class, 'paymentGatewaySettings'])->name('settings.payment');
        Route::post('/settings/payment', [\App\Http\Controllers\AdminController::class, 'updatePaymentGatewaySettings'])->name('settings.payment.update');
        Route::get('/settings/theme', [\App\Http\Controllers\AdminController::class, 'themeSettings'])->name('settings.theme');
        Route::post('/settings/theme', [\App\Http\Controllers\AdminController::class, 'updateThemeSettings'])->name('settings.theme.update');
        Route::get('/settings/oauth', [\App\Http\Controllers\AdminController::class, 'oauthSettings'])->name('settings.oauth');
        Route::post('/settings/oauth', [\App\Http\Controllers\AdminController::class, 'updateOauthSettings'])->name('settings.oauth.update');
        Route::get('/settings/credit-topup', [\App\Http\Controllers\AdminController::class, 'creditTopupSettings'])->name('settings.credit-topup');
        Route::post('/settings/credit-topup', [\App\Http\Controllers\AdminController::class, 'updateCreditTopupSettings'])->name('settings.credit-topup.update');
        Route::get('/settings/special-offers-modal', [\App\Http\Controllers\AdminController::class, 'specialOffersModalSettings'])->name('settings.special-offers-modal');
        Route::post('/settings/special-offers-modal', [\App\Http\Controllers\AdminController::class, 'updateSpecialOffersModalSettings'])->name('settings.special-offers-modal.update');
        Route::get('/settings/session-recording', [\App\Http\Controllers\AdminController::class, 'sessionRecordingSettings'])->name('settings.session-recording');
        Route::post('/settings/session-recording', [\App\Http\Controllers\AdminController::class, 'updateSessionRecordingSettings'])->name('settings.session-recording.update');

        Route::get('/seo', [\App\Http\Controllers\AdminSeoController::class, 'index'])->name('seo.index');
        Route::put('/seo/global', [\App\Http\Controllers\AdminSeoController::class, 'updateGlobal'])->name('seo.global');
        Route::post('/seo/score-preview', [\App\Http\Controllers\AdminSeoController::class, 'scorePreview'])->name('seo.score-preview');
        Route::post('/seo/sync-registry', [\App\Http\Controllers\AdminSeoController::class, 'syncRegistry'])->name('seo.sync-registry');
        Route::get('/seo/{seoPage}/edit', [\App\Http\Controllers\AdminSeoController::class, 'edit'])->name('seo.edit');
        Route::put('/seo/{seoPage}', [\App\Http\Controllers\AdminSeoController::class, 'update'])->name('seo.update');

        Route::get('/settings/backup-restore', [\App\Http\Controllers\AdminBackupController::class, 'index'])->name('settings.backup-restore');
        Route::post('/settings/backup-restore/backup', [\App\Http\Controllers\AdminBackupController::class, 'createBackup'])->name('settings.backup-restore.backup');
        Route::post('/settings/backup-restore/restore', [\App\Http\Controllers\AdminBackupController::class, 'uploadRestore'])->name('settings.backup-restore.restore');
        Route::get('/settings/backup-restore/download/{backupOperation}', [\App\Http\Controllers\AdminBackupController::class, 'download'])->name('settings.backup-restore.download');
        Route::get('/settings/backup-restore/status/{backupOperation}', [\App\Http\Controllers\AdminBackupController::class, 'status'])->name('settings.backup-restore.status');

        // Sheet Types CRUD
        Route::get('/sheet-types', [\App\Http\Controllers\AdminController::class, 'sheetTypes'])->name('sheet-types');
        Route::get('/sheet-types/create', [\App\Http\Controllers\AdminController::class, 'createSheetType'])->name('sheet-types.create');
        Route::post('/sheet-types/generate-description', [\App\Http\Controllers\AdminController::class, 'generateSheetTypeDescription'])->name('sheet-types.generateDescription');
        Route::post('/sheet-types', [\App\Http\Controllers\AdminController::class, 'storeSheetType'])->name('sheet-types.store');
        Route::get('/sheet-types/{id}/edit', [\App\Http\Controllers\AdminController::class, 'editSheetType'])->name('sheet-types.edit');
        Route::put('/sheet-types/{id}', [\App\Http\Controllers\AdminController::class, 'updateSheetType'])->name('sheet-types.update');
        Route::delete('/sheet-types/{id}', [\App\Http\Controllers\AdminController::class, 'deleteSheetType'])->name('sheet-types.delete');

        Route::get('/envelope-types', [\App\Http\Controllers\AdminController::class, 'envelopeTypes'])->name('envelope-types');
        Route::get('/envelope-types/create', [\App\Http\Controllers\AdminController::class, 'createEnvelopeType'])->name('envelope-types.create');
        Route::post('/envelope-types', [\App\Http\Controllers\AdminController::class, 'storeEnvelopeType'])->name('envelope-types.store');
        Route::get('/envelope-types/{id}/edit', [\App\Http\Controllers\AdminController::class, 'editEnvelopeType'])->name('envelope-types.edit');
        Route::put('/envelope-types/{id}', [\App\Http\Controllers\AdminController::class, 'updateEnvelopeType'])->name('envelope-types.update');
        Route::delete('/envelope-types/{id}', [\App\Http\Controllers\AdminController::class, 'deleteEnvelopeType'])->name('envelope-types.delete');

        Route::get('/stock', [\App\Http\Controllers\AdminStockController::class, 'index'])->name('stock.index');
        Route::get('/stock/purchases', [\App\Http\Controllers\AdminStockController::class, 'purchasesIndex'])->name('stock.purchases.index');
        Route::get('/stock/purchases/create', [\App\Http\Controllers\AdminStockController::class, 'createPurchase'])->name('stock.purchases.create');
        Route::post('/stock/purchases', [\App\Http\Controllers\AdminStockController::class, 'storePurchase'])->name('stock.purchases.store');
        Route::get('/stock/purchases/{purchase}', [\App\Http\Controllers\AdminStockController::class, 'showPurchase'])->name('stock.purchases.show');

        Route::get('/suppliers', [\App\Http\Controllers\AdminSupplierController::class, 'index'])->name('suppliers.index');
        Route::get('/suppliers/create', [\App\Http\Controllers\AdminSupplierController::class, 'create'])->name('suppliers.create');
        Route::post('/suppliers', [\App\Http\Controllers\AdminSupplierController::class, 'store'])->name('suppliers.store');
        Route::get('/suppliers/{supplier}/edit', [\App\Http\Controllers\AdminSupplierController::class, 'edit'])->name('suppliers.edit');
        Route::put('/suppliers/{supplier}', [\App\Http\Controllers\AdminSupplierController::class, 'update'])->name('suppliers.update');
        Route::delete('/suppliers/{supplier}', [\App\Http\Controllers\AdminSupplierController::class, 'destroy'])->name('suppliers.destroy');

        // Template Licenses CRUD
        Route::get('/licenses', [\App\Http\Controllers\AdminController::class, 'licenses'])->name('licenses');
        Route::get('/licenses/create', [\App\Http\Controllers\AdminController::class, 'createLicense'])->name('licenses.create');
        Route::post('/licenses', [\App\Http\Controllers\AdminController::class, 'storeLicense'])->name('licenses.store');
        Route::get('/licenses/{id}/edit', [\App\Http\Controllers\AdminController::class, 'editLicense'])->name('licenses.edit');
        Route::put('/licenses/{id}', [\App\Http\Controllers\AdminController::class, 'updateLicense'])->name('licenses.update');
        Route::delete('/licenses/{id}', [\App\Http\Controllers\AdminController::class, 'deleteLicense'])->name('licenses.delete');

        Route::get('/thumbnail-prompts', [\App\Http\Controllers\AdminController::class, 'thumbnailPrompts'])->name('thumbnail-prompts');
        Route::get('/thumbnail-prompts/create', [\App\Http\Controllers\AdminController::class, 'createThumbnailPrompt'])->name('thumbnail-prompts.create');
        Route::post('/thumbnail-prompts', [\App\Http\Controllers\AdminController::class, 'storeThumbnailPrompt'])->name('thumbnail-prompts.store');
        Route::get('/thumbnail-prompts/{id}/edit', [\App\Http\Controllers\AdminController::class, 'editThumbnailPrompt'])->name('thumbnail-prompts.edit');
        Route::put('/thumbnail-prompts/{id}', [\App\Http\Controllers\AdminController::class, 'updateThumbnailPrompt'])->name('thumbnail-prompts.update');
        Route::delete('/thumbnail-prompts/{id}', [\App\Http\Controllers\AdminController::class, 'deleteThumbnailPrompt'])->name('thumbnail-prompts.delete');

        Route::get('/design-intro', [\App\Http\Controllers\AdminController::class, 'designIntro'])->name('design-intro');
        Route::post('/design-intro/settings', [\App\Http\Controllers\AdminController::class, 'updateDesignIntroSettings'])->name('design-intro.settings');
        Route::post('/design-intro/explore-settings', [\App\Http\Controllers\AdminController::class, 'updateDesignIntroExploreSettings'])->name('design-intro.explore-settings');
        Route::get('/design-intro/steps/create', [\App\Http\Controllers\AdminController::class, 'createIntroTourStep'])->name('design-intro.steps.create');
        Route::post('/design-intro/steps', [\App\Http\Controllers\AdminController::class, 'storeIntroTourStep'])->name('design-intro.steps.store');
        Route::get('/design-intro/steps/{id}/edit', [\App\Http\Controllers\AdminController::class, 'editIntroTourStep'])->name('design-intro.steps.edit');
        Route::put('/design-intro/steps/{id}', [\App\Http\Controllers\AdminController::class, 'updateIntroTourStep'])->name('design-intro.steps.update');
        Route::delete('/design-intro/steps/{id}', [\App\Http\Controllers\AdminController::class, 'deleteIntroTourStep'])->name('design-intro.steps.delete');

        Route::get('/template-categories', [\App\Http\Controllers\AdminController::class, 'templateCategories'])->name('template-categories');
        Route::get('/template-categories/create', [\App\Http\Controllers\AdminController::class, 'createTemplateCategory'])->name('template-categories.create');
        Route::post('/template-categories', [\App\Http\Controllers\AdminController::class, 'storeTemplateCategory'])->name('template-categories.store');
        Route::get('/template-categories/{id}/edit', [\App\Http\Controllers\AdminController::class, 'editTemplateCategory'])->name('template-categories.edit');
        Route::put('/template-categories/{id}', [\App\Http\Controllers\AdminController::class, 'updateTemplateCategory'])->name('template-categories.update');
        Route::delete('/template-categories/{id}', [\App\Http\Controllers\AdminController::class, 'deleteTemplateCategory'])->name('template-categories.delete');

        // Currencies CRUD (Settings)
        Route::get('/currencies', [\App\Http\Controllers\AdminController::class, 'currencies'])->name('currencies');
        Route::get('/currencies/create', [\App\Http\Controllers\AdminController::class, 'createCurrency'])->name('currencies.create');
        Route::post('/currencies', [\App\Http\Controllers\AdminController::class, 'storeCurrency'])->name('currencies.store');
        Route::get('/currencies/{id}/edit', [\App\Http\Controllers\AdminController::class, 'editCurrency'])->name('currencies.edit');
        Route::put('/currencies/{id}', [\App\Http\Controllers\AdminController::class, 'updateCurrency'])->name('currencies.update');
        Route::delete('/currencies/{id}', [\App\Http\Controllers\AdminController::class, 'deleteCurrency'])->name('currencies.delete');

        // Explore Page Slider CRUD
        Route::get('/explore-slides', [\App\Http\Controllers\AdminController::class, 'exploreSlides'])->name('explore-slides');
        Route::get('/explore-slides/create', [\App\Http\Controllers\AdminController::class, 'createExploreSlide'])->name('explore-slides.create');
        Route::post('/explore-slides', [\App\Http\Controllers\AdminController::class, 'storeExploreSlide'])->name('explore-slides.store');
        Route::get('/explore-slides/{id}/edit', [\App\Http\Controllers\AdminController::class, 'editExploreSlide'])->name('explore-slides.edit');
        Route::put('/explore-slides/{id}', [\App\Http\Controllers\AdminController::class, 'updateExploreSlide'])->name('explore-slides.update');
        Route::delete('/explore-slides/{id}', [\App\Http\Controllers\AdminController::class, 'deleteExploreSlide'])->name('explore-slides.delete');

        Route::post('/templates/{id}/toggle-featured', [\App\Http\Controllers\AdminController::class, 'toggleTemplateFeatured'])->name('templates.toggle-featured');

        // Pricing Rules CRUD (cost breakdown / checkout rules)
        Route::get('/pricing-rules', [\App\Http\Controllers\AdminController::class, 'pricingRules'])->name('pricing-rules');
        Route::get('/pricing-rules/create', [\App\Http\Controllers\AdminController::class, 'createPricingRule'])->name('pricing-rules.create');
        Route::post('/pricing-rules', [\App\Http\Controllers\AdminController::class, 'storePricingRule'])->name('pricing-rules.store');
        Route::get('/pricing-rules/{id}/edit', [\App\Http\Controllers\AdminController::class, 'editPricingRule'])->name('pricing-rules.edit');
        Route::put('/pricing-rules/{id}', [\App\Http\Controllers\AdminController::class, 'updatePricingRule'])->name('pricing-rules.update');
        Route::delete('/pricing-rules/{id}', [\App\Http\Controllers\AdminController::class, 'deletePricingRule'])->name('pricing-rules.delete');

        // AI Content Templates CRUD
        Route::get('/ai-content-templates', [\App\Http\Controllers\AdminController::class, 'aiContentTemplates'])->name('ai-content-templates');
        Route::get('/ai-content-templates/create', [\App\Http\Controllers\AdminController::class, 'createAiContentTemplate'])->name('ai-content-templates.create');
        Route::post('/ai-content-templates', [\App\Http\Controllers\AdminController::class, 'storeAiContentTemplate'])->name('ai-content-templates.store');
        Route::get('/ai-content-templates/{id}/edit', [\App\Http\Controllers\AdminController::class, 'editAiContentTemplate'])->name('ai-content-templates.edit');
        Route::put('/ai-content-templates/{id}', [\App\Http\Controllers\AdminController::class, 'updateAiContentTemplate'])->name('ai-content-templates.update');
        Route::delete('/ai-content-templates/{id}', [\App\Http\Controllers\AdminController::class, 'deleteAiContentTemplate'])->name('ai-content-templates.delete');

        // Testimonials CRUD (home page)
        Route::get('/testimonials', [\App\Http\Controllers\AdminController::class, 'testimonials'])->name('testimonials');
        Route::get('/testimonials/create', [\App\Http\Controllers\AdminController::class, 'createTestimonial'])->name('testimonials.create');
        Route::post('/testimonials', [\App\Http\Controllers\AdminController::class, 'storeTestimonial'])->name('testimonials.store');
        Route::get('/testimonials/{id}/edit', [\App\Http\Controllers\AdminController::class, 'editTestimonial'])->name('testimonials.edit');
        Route::put('/testimonials/{id}', [\App\Http\Controllers\AdminController::class, 'updateTestimonial'])->name('testimonials.update');
        Route::delete('/testimonials/{id}', [\App\Http\Controllers\AdminController::class, 'deleteTestimonial'])->name('testimonials.delete');
        Route::get('/documentation', [\App\Http\Controllers\AdminController::class, 'documentation'])->name('documentation');
        Route::get('/documentation/create', [\App\Http\Controllers\AdminController::class, 'createDocumentation'])->name('documentation.create');
        Route::post('/documentation', [\App\Http\Controllers\AdminController::class, 'storeDocumentation'])->name('documentation.store');
        Route::get('/documentation/{id}/edit', [\App\Http\Controllers\AdminController::class, 'editDocumentation'])->name('documentation.edit');
        Route::put('/documentation/{id}', [\App\Http\Controllers\AdminController::class, 'updateDocumentation'])->name('documentation.update');
        Route::delete('/documentation/{id}', [\App\Http\Controllers\AdminController::class, 'deleteDocumentation'])->name('documentation.delete');
        Route::get('/documentation-categories', [\App\Http\Controllers\AdminController::class, 'documentationCategories'])->name('documentation-categories');
        Route::get('/documentation-categories/create', [\App\Http\Controllers\AdminController::class, 'createDocumentationCategory'])->name('documentation-categories.create');
        Route::post('/documentation-categories', [\App\Http\Controllers\AdminController::class, 'storeDocumentationCategory'])->name('documentation-categories.store');
        Route::get('/documentation-categories/{id}/edit', [\App\Http\Controllers\AdminController::class, 'editDocumentationCategory'])->name('documentation-categories.edit');
        Route::put('/documentation-categories/{id}', [\App\Http\Controllers\AdminController::class, 'updateDocumentationCategory'])->name('documentation-categories.update');
        Route::delete('/documentation-categories/{id}', [\App\Http\Controllers\AdminController::class, 'deleteDocumentationCategory'])->name('documentation-categories.delete');

        // Products CRUD
        Route::get('/products', [\App\Http\Controllers\AdminController::class, 'products'])->name('products');
        Route::post('/products/generate-faq', [\App\Http\Controllers\AdminController::class, 'generateProductFaq'])->name('products.generateFaq');
        Route::post('/products/generate-description-and-faq', [\App\Http\Controllers\AdminController::class, 'generateProductDescriptionAndFaq'])->name('products.generateDescriptionAndFaq');
        Route::get('/products/create', [\App\Http\Controllers\AdminController::class, 'createProduct'])->name('products.create');
        Route::post('/products', [\App\Http\Controllers\AdminController::class, 'storeProduct'])->name('products.store');
        Route::get('/products/{id}/edit', [\App\Http\Controllers\AdminController::class, 'editProduct'])->name('products.edit');
        Route::put('/products/{id}', [\App\Http\Controllers\AdminController::class, 'updateProduct'])->name('products.update');
        Route::delete('/products/{id}', [\App\Http\Controllers\AdminController::class, 'deleteProduct'])->name('products.delete');

        // Global Image Library (category-wise)
        Route::get('/global-images', [\App\Http\Controllers\Admin\GlobalImageController::class, 'index'])->name('global-images.index');
        Route::get('/global-images/categories/create', [\App\Http\Controllers\Admin\GlobalImageController::class, 'createCategory'])->name('global-images.categories.create');
        Route::post('/global-images/categories', [\App\Http\Controllers\Admin\GlobalImageController::class, 'storeCategory'])->name('global-images.categories.store');
        Route::get('/global-images/categories/{id}/edit', [\App\Http\Controllers\Admin\GlobalImageController::class, 'editCategory'])->name('global-images.categories.edit');
        Route::put('/global-images/categories/{id}', [\App\Http\Controllers\Admin\GlobalImageController::class, 'updateCategory'])->name('global-images.categories.update');
        Route::delete('/global-images/categories/{id}', [\App\Http\Controllers\Admin\GlobalImageController::class, 'deleteCategory'])->name('global-images.categories.delete');
        Route::get('/global-images/categories/{id}', [\App\Http\Controllers\Admin\GlobalImageController::class, 'showCategory'])->name('global-images.show');
        Route::post('/global-images/categories/{id}/upload', [\App\Http\Controllers\Admin\GlobalImageController::class, 'uploadImages'])->name('global-images.upload');
        Route::delete('/global-images/categories/{categoryId}/images/{imageId}', [\App\Http\Controllers\Admin\GlobalImageController::class, 'deleteImage'])->name('global-images.images.delete');

        Route::get('/design-fonts', [\App\Http\Controllers\Admin\DesignFontController::class, 'index'])->name('design-fonts.index');
        Route::get('/design-fonts/create', [\App\Http\Controllers\Admin\DesignFontController::class, 'create'])->name('design-fonts.create');
        Route::post('/design-fonts', [\App\Http\Controllers\Admin\DesignFontController::class, 'store'])->name('design-fonts.store');
        Route::get('/design-fonts/{id}/edit', [\App\Http\Controllers\Admin\DesignFontController::class, 'edit'])->name('design-fonts.edit');
        Route::put('/design-fonts/{id}', [\App\Http\Controllers\Admin\DesignFontController::class, 'update'])->name('design-fonts.update');
        Route::delete('/design-fonts/{id}', [\App\Http\Controllers\Admin\DesignFontController::class, 'destroy'])->name('design-fonts.destroy');

        // Modules management
        Route::get('/modules', [\App\Http\Controllers\Admin\ModuleController::class, 'index'])->name('modules.index');
        Route::post('/modules', [\App\Http\Controllers\Admin\ModuleController::class, 'store'])->name('modules.store');
        Route::patch('/modules/{name}/toggle', [\App\Http\Controllers\Admin\ModuleController::class, 'toggle'])->name('modules.toggle');
        Route::delete('/modules/{name}', [\App\Http\Controllers\Admin\ModuleController::class, 'destroy'])->name('modules.destroy');
    });
});
