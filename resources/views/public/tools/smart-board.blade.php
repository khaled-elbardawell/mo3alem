@extends('layouts.app', ['fullWidth' => true, 'immersive' => true])

@section('htmlClass', 'smart-board-page')

@section('content')
    <div id="smartBoardConfig" hidden
        data-config="{{ json_encode($smartBoardConfig, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) }}">
    </div>

    <main class="smart-board-app" id="smartBoardApp" data-smart-board>
        <header class="smart-board-topbar" aria-label="شريط تحكم السبورة">
            <a class="smart-board-icon-button" href="{{ route('home') }}" aria-label="العودة إلى الرئيسية" title="الرئيسية">
                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
            </a>
            <div class="min-w-0 flex-1">
                <label class="sr-only" for="boardTitleInput">اسم السبورة</label>
                <input class="smart-board-title" id="boardTitleInput" maxlength="120" value="سبورتي الذكية"
                    autocomplete="off">
                <p class="smart-board-save-status" id="boardSaveStatus" role="status">محفوظة على هذا الجهاز</p>
            </div>
            <div class="smart-board-top-actions">
                <button class="smart-board-icon-button" id="undoBoardBtn" type="button" aria-label="تراجع" title="تراجع"
                    disabled><i class="fa-solid fa-rotate-left" aria-hidden="true"></i></button>
                <button class="smart-board-icon-button" id="redoBoardBtn" type="button" aria-label="إعادة" title="إعادة"
                    disabled><i class="fa-solid fa-rotate-right" aria-hidden="true"></i></button>
                <button class="smart-board-icon-button" id="boardsLibraryBtn" type="button" aria-label="سبوراتي"
                    title="سبوراتي"><i class="fa-solid fa-folder-open" aria-hidden="true"></i></button>
                <button class="smart-board-icon-button" id="newBoardBtn" type="button" aria-label="سبورة جديدة"
                    title="سبورة جديدة"><i class="fa-solid fa-file-circle-plus" aria-hidden="true"></i></button>
                <button class="smart-board-icon-button is-danger" id="clearBoardBtn" type="button"
                    aria-label="تنظيف كل محتوى السبورة" title="تنظيف السبورة"><i class="fa-solid fa-broom"
                        aria-hidden="true"></i></button>
                <button class="smart-board-primary-button" id="saveBoardBtn" type="button">
                    <i class="fa-solid fa-cloud-arrow-up" aria-hidden="true"></i>
                    <span>حفظ</span>
                </button>
                <button class="smart-board-secondary-button" id="presentationModeBtn" type="button">
                    <i class="fa-solid fa-display" aria-hidden="true"></i>
                    <span>وضع العرض</span>
                </button>
                <button class="smart-board-icon-button" id="fullscreenBoardBtn" type="button" aria-label="ملء الشاشة"
                    title="ملء الشاشة"><i class="fa-solid fa-expand" aria-hidden="true"></i></button>
            </div>
        </header>

        <section class="smart-board-stage-shell">
            <div class="smart-board-stage" id="smartBoardStage" tabindex="0" aria-label="مساحة السبورة الذكية">
                <div class="smart-board-grid" aria-hidden="true"></div>
                <div id="smartBoardWidgets"></div>
                <canvas class="smart-board-drawing-layer" id="smartBoardDrawingCanvas"
                    aria-label="طبقة الرسم على كامل السبورة"></canvas>
                <div class="smart-board-selection-toolbar" id="selectionToolbar" hidden>
                    <button type="button" data-selection-action="edit" aria-label="تعديل الأداة"><i
                            class="fa-solid fa-sliders" aria-hidden="true"></i></button>
                    <button type="button" data-selection-action="duplicate" aria-label="نسخ الأداة"><i
                            class="fa-regular fa-copy" aria-hidden="true"></i></button>
                    <button type="button" data-selection-action="lock" aria-label="قفل الأداة"><i
                            class="fa-solid fa-lock-open" aria-hidden="true"></i></button>
                    <button type="button" data-selection-action="front" aria-label="نقل إلى الأمام"><i
                            class="fa-solid fa-layer-group" aria-hidden="true"></i></button>
                    <button class="danger" type="button" data-selection-action="delete" aria-label="حذف الأداة"><i
                            class="fa-solid fa-trash" aria-hidden="true"></i></button>
                </div>
            </div>
        </section>

        <div class="smart-board-drawing-toolbar" id="smartBoardDrawingToolbar" aria-label="أدوات الرسم" hidden>
            <button type="button" data-drawing-tool="pen" aria-label="قلم" title="قلم">
                <i class="fa-solid fa-pen" aria-hidden="true"></i>
            </button>
            <button type="button" data-drawing-tool="highlight" aria-label="قلم تظليل" title="قلم تظليل">
                <i class="fa-solid fa-highlighter" aria-hidden="true"></i>
            </button>
            <button type="button" data-drawing-tool="eraser" aria-label="ممحاة" title="ممحاة">
                <i class="fa-solid fa-eraser" aria-hidden="true"></i>
            </button>
            <label class="smart-board-drawing-color" title="لون القلم">
                <span class="sr-only">لون القلم</span>
                <input id="smartBoardDrawingColor" type="color" value="#6d28d9">
            </label>
            <label class="smart-board-drawing-width">
                <i class="fa-solid fa-circle" aria-hidden="true"></i>
                <span class="sr-only">سماكة الخط</span>
                <input id="smartBoardDrawingWidth" type="range" min="1" max="24" value="4">
                <output id="smartBoardDrawingWidthValue">4</output>
            </label>
            <button class="danger" id="clearBoardDrawingBtn" type="button" aria-label="مسح كل الرسومات"
                title="مسح كل الرسومات">
                <i class="fa-solid fa-trash" aria-hidden="true"></i>
            </button>
        </div>

        <nav class="smart-board-dock" id="smartBoardDock" aria-label="أدوات السبورة">
            <div class="smart-board-mode-switch" role="group" aria-label="وضع التفاعل">
                <button class="smart-board-tool-button is-active" type="button" data-board-mode="move"
                    aria-pressed="true" aria-label="وضع التحريك">
                    <i class="fa-solid fa-arrow-pointer" aria-hidden="true"></i>
                    <span>تحريك</span>
                </button>
                <button class="smart-board-tool-button" type="button" data-board-mode="draw"
                    aria-pressed="false" aria-label="وضع الرسم">
                    <i class="fa-solid fa-pencil" aria-hidden="true"></i>
                    <span>رسم</span>
                </button>
            </div>
            <span class="smart-board-dock-divider" aria-hidden="true"></span>
            <div class="smart-board-dock-tools" id="smartBoardDockTools">
                @foreach([
                    ['text', 'fa-font', 'نص'],
                    ['clock', 'fa-clock', 'ساعة'],
                    ['timer', 'fa-hourglass-half', 'مؤقت'],
                    ['stopwatch', 'fa-stopwatch', 'إيقاف'],
                    ['traffic-light', 'fa-traffic-light', 'إشارة'],
                    ['scoreboard', 'fa-ranking-star', 'نتائج'],
                    ['dice', 'fa-dice', 'نرد'],
                    ['randomizer', 'fa-shuffle', 'اختيار'],
                    ['image', 'fa-image', 'صورة'],
                ] as [$type, $icon, $label])
                    <button class="smart-board-tool-button" type="button" data-add-widget data-widget-type="{{ $type }}"
                        data-dock-priority="{{ $loop->iteration }}" aria-label="إضافة {{ $label }}">
                        <i class="fa-solid {{ $icon }}" aria-hidden="true"></i>
                        <span>{{ $label }}</span>
                    </button>
                @endforeach
                <button class="smart-board-tool-button smart-board-more-toggle" id="smartBoardMoreBtn" type="button"
                    aria-controls="smartBoardMoreMenu" aria-expanded="false" aria-haspopup="true"
                    aria-label="عرض المزيد من الأدوات">
                    <i class="fa-solid fa-grip" aria-hidden="true"></i>
                    <span>المزيد</span>
                </button>
            </div>
            <button class="smart-board-dock-toggle" id="toggleBoardDockBtn" type="button"
                aria-controls="smartBoardDockTools" aria-expanded="true" aria-label="تصغير شريط الأدوات"
                title="تصغير شريط الأدوات">
                <i class="fa-solid fa-angles-down" aria-hidden="true"></i>
            </button>

            <section class="smart-board-more-menu" id="smartBoardMoreMenu" aria-label="كل أدوات السبورة" hidden>
                <div class="smart-board-more-heading">
                    <div>
                        <strong>كل الأدوات</strong>
                        <span>اختر أداة لإضافتها إلى منتصف السبورة</span>
                    </div>
                    <button type="button" data-close-more aria-label="إغلاق قائمة الأدوات"><i
                            class="fa-solid fa-xmark" aria-hidden="true"></i></button>
                </div>
                <div class="smart-board-more-grid">
                    @foreach([
                        ['text', 'fa-font', 'نص'],
                        ['clock', 'fa-clock', 'ساعة'],
                        ['timer', 'fa-hourglass-half', 'مؤقت'],
                        ['stopwatch', 'fa-stopwatch', 'إيقاف'],
                        ['traffic-light', 'fa-traffic-light', 'إشارة'],
                        ['scoreboard', 'fa-ranking-star', 'نتائج'],
                        ['dice', 'fa-dice', 'نرد'],
                        ['randomizer', 'fa-shuffle', 'اختيار'],
                        ['groups', 'fa-users-rectangle', 'مجموعات'],
                        ['image', 'fa-image', 'صورة'],
                        ['embed', 'fa-circle-play', 'فيديو'],
                        ['qr', 'fa-qrcode', 'QR'],
                        ['poll', 'fa-square-poll-vertical', 'استطلاع'],
                    ] as [$type, $icon, $label])
                        <button class="smart-board-more-tool" type="button" data-add-widget data-widget-type="{{ $type }}"
                            aria-label="إضافة {{ $label }}">
                            <i class="fa-solid {{ $icon }}" aria-hidden="true"></i>
                            <span>{{ $label }}</span>
                        </button>
                    @endforeach
                    <button class="smart-board-more-tool" id="boardAppearanceBtn" type="button"
                        aria-label="مظهر السبورة">
                        <i class="fa-solid fa-palette" aria-hidden="true"></i>
                        <span>المظهر</span>
                    </button>
                </div>
            </section>
        </nav>

        <input id="boardImageInput" type="file" accept="image/jpeg,image/png,image/webp" hidden>

        <dialog class="smart-board-dialog" id="widgetSettingsDialog">
            <form method="dialog">
                <div class="smart-board-dialog-heading">
                    <div>
                        <p class="smart-board-eyebrow">تخصيص سريع</p>
                        <h2 id="widgetSettingsTitle">إعدادات الأداة</h2>
                    </div>
                    <button class="smart-board-icon-button" value="cancel" aria-label="إغلاق"><i
                            class="fa-solid fa-xmark" aria-hidden="true"></i></button>
                </div>
                <div class="smart-board-settings-content" id="widgetSettingsContent"></div>
                <div class="smart-board-dialog-actions">
                    <button class="smart-board-primary-button" id="applyWidgetSettingsBtn" value="default">تطبيق</button>
                    <button class="smart-board-secondary-button" value="cancel">إلغاء</button>
                </div>
            </form>
        </dialog>

        <dialog class="smart-board-dialog" id="appearanceDialog">
            <form method="dialog">
                <div class="smart-board-dialog-heading">
                    <div>
                        <p class="smart-board-eyebrow">شكل يناسب حصتك</p>
                        <h2>مظهر السبورة</h2>
                    </div>
                    <button class="smart-board-icon-button" value="cancel" aria-label="إغلاق"><i
                            class="fa-solid fa-xmark" aria-hidden="true"></i></button>
                </div>
                <fieldset class="smart-board-appearance-section">
                    <legend>الخلفيات الأساسية</legend>
                    <div class="smart-board-background-grid">
                        @foreach([
                            ['plain', 'أبيض', 'fa-square'],
                            ['color', 'لون مخصص', 'fa-palette'],
                            ['aurora', 'بنفسجي هادئ', 'fa-wand-magic-sparkles'],
                            ['sky', 'سماء صافية', 'fa-cloud-sun'],
                            ['paper', 'ورق مربعات', 'fa-border-all'],
                            ['night', 'ليلي', 'fa-moon'],
                        ] as [$value, $label, $icon])
                            <label class="smart-board-background-choice" data-background-choice="{{ $value }}">
                                <input type="radio" name="board_background" value="{{ $value }}">
                                <span class="smart-board-background-swatch" data-background-preview="{{ $value }}">
                                    <i class="fa-solid {{ $icon }}" aria-hidden="true"></i>
                                </span>
                                <strong>{{ $label }}</strong>
                            </label>
                        @endforeach
                    </div>
                    <label class="smart-board-color-setting" id="boardCustomColorField" hidden>
                        <span>اختر لون الخلفية</span>
                        <input id="boardBackgroundColor" type="color" value="#ffffff">
                    </label>
                </fieldset>

                <fieldset class="smart-board-appearance-section">
                    <legend>خلفيات صور جاهزة</legend>
                    <div class="smart-board-image-background-grid">
                        @foreach([
                            ['image-mountains', 'جبال هادئة', 'mountains.svg'],
                            ['image-clouds', 'سماء وغيوم', 'clouds.svg'],
                            ['image-shapes', 'أشكال مرحة', 'shapes.svg'],
                            ['image-classroom', 'صف هادئ', 'classroom.svg'],
                        ] as [$value, $label, $image])
                            <label class="smart-board-image-background-choice">
                                <input type="radio" name="board_background" value="{{ $value }}">
                                <span data-background-image="{{ asset("images/smart-board/backgrounds/{$image}") }}"
                                    style="background-image: url('{{ asset("images/smart-board/backgrounds/{$image}") }}')"></span>
                                <strong>{{ $label }}</strong>
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                <fieldset class="smart-board-appearance-section">
                    <legend>صورة من جهازك</legend>
                    <input class="sr-only" type="radio" name="board_background" value="custom-image">
                    <div class="smart-board-background-upload">
                        <div class="smart-board-background-upload-preview">
                            <img id="boardBackgroundImagePreview" alt="معاينة صورة الخلفية" hidden>
                            <div id="boardBackgroundImageEmpty">
                                <i class="fa-regular fa-image" aria-hidden="true"></i>
                                <span>ارفع صورة لتظهر هنا</span>
                            </div>
                        </div>
                        <div class="smart-board-background-upload-actions">
                            <label class="smart-board-primary-button" for="boardBackgroundImageInput">
                                <i class="fa-solid fa-arrow-up-from-bracket" aria-hidden="true"></i>
                                اختيار صورة
                            </label>
                            <input class="sr-only" id="boardBackgroundImageInput" type="file"
                                accept="image/jpeg,image/png,image/webp">
                            <button class="smart-board-secondary-button" id="removeBoardBackgroundImageBtn" type="button" hidden>
                                إزالة الصورة
                            </button>
                            <small id="boardBackgroundImageName">لم تُرفع صورة بعد</small>
                            <small>JPG أو PNG أو WebP، بحد أقصى 5MB.</small>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="smart-board-appearance-section">
                    <legend>إعدادات الخلفية العامة</legend>
                    <div class="smart-board-general-settings">
                        <label class="smart-board-field">نسبة العرض
                            <select id="boardAspectRatio">
                                <option value="auto">تلقائية</option>
                                <option value="16/9">16:9</option>
                                <option value="4/3">4:3</option>
                            </select>
                        </label>
                        <label class="smart-board-field" id="boardImageDisplaySettings">ملاءمة الصورة
                            <select id="boardBackgroundSize">
                                <option value="contain">إظهار الصورة كاملة — بدون قص</option>
                                <option value="cover">تعبئة الشاشة — قد تقص الأطراف</option>
                            </select>
                        </label>
                        <label class="smart-board-range-setting">
                            <span>تهدئة الخلفية</span>
                            <input id="boardBackgroundOverlay" type="range" min="0" max="70" step="5" value="18">
                            <output id="boardBackgroundOverlayValue">18%</output>
                        </label>
                        <label class="smart-board-toggle-setting">
                            <input id="boardShowGrid" type="checkbox">
                            <span><strong>شبكة إرشادية</strong><small>نقاط خفيفة تساعد في ترتيب الأدوات</small></span>
                        </label>
                    </div>
                </fieldset>
                <fieldset class="smart-board-background-options">
                    <legend class="font-black text-slate-800">قالب بداية</legend>
                    <div class="grid grid-cols-3 gap-2">
                        <button class="smart-board-inline-action" type="button" data-board-template="welcome">ترحيب</button>
                        <button class="smart-board-inline-action" type="button" data-board-template="activity">نشاط</button>
                        <button class="smart-board-inline-action" type="button" data-board-template="quiz">اختبار</button>
                    </div>
                </fieldset>
                <div class="smart-board-dialog-actions">
                    <button class="smart-board-primary-button" id="applyAppearanceBtn" value="default">تطبيق</button>
                    <button class="smart-board-secondary-button" value="cancel">إلغاء</button>
                </div>
            </form>
        </dialog>

        <dialog class="smart-board-dialog smart-board-library-dialog" id="boardsLibraryDialog">
            <form method="dialog">
                <div class="smart-board-dialog-heading">
                    <div>
                        <p class="smart-board-eyebrow">كل حصصك في مكان واحد</p>
                        <h2>سبوراتي</h2>
                    </div>
                    <button class="smart-board-icon-button" value="cancel" aria-label="إغلاق"><i
                            class="fa-solid fa-xmark" aria-hidden="true"></i></button>
                </div>
                <div class="smart-board-library" id="boardsLibraryContent"></div>
            </form>
        </dialog>

        <dialog class="smart-board-dialog" id="sessionDialog">
            <form method="dialog">
                <div class="smart-board-dialog-heading">
                    <div>
                        <p class="smart-board-eyebrow">شارك طلابك فورًا</p>
                        <h2>جلسة الاستطلاع</h2>
                    </div>
                    <button class="smart-board-icon-button" value="cancel" aria-label="إغلاق"><i
                            class="fa-solid fa-xmark" aria-hidden="true"></i></button>
                </div>
                <div id="sessionDialogContent"></div>
            </form>
        </dialog>

        <dialog class="smart-board-dialog smart-board-onboarding" id="onboardingDialog">
            <form method="dialog">
                <div class="smart-board-onboarding-icon"><i class="fa-solid fa-wand-magic-sparkles"
                        aria-hidden="true"></i></div>
                <h2>سبورتك جاهزة خلال لحظات</h2>
                <ol>
                    <li><strong>أضف أداة</strong><span>اخترها من الشريط السفلي.</span></li>
                    <li><strong>رتّبها بحرية</strong><span>اسحبها وكبّرها كما يناسب الحصة.</span></li>
                    <li><strong>اعرضها للطلاب</strong><span>اضغط وضع العرض لشاشة نظيفة.</span></li>
                </ol>
                <button class="smart-board-primary-button w-full justify-center" value="default">ابدأ الآن</button>
            </form>
        </dialog>

        <div class="smart-board-toast" id="smartBoardToast" role="status" aria-live="polite"></div>
    </main>
@endsection
