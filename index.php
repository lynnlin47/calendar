<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Hyper-Faceted Personal Operating System Calendar</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = {
  theme: {
    extend: {
      fontFamily: {
        sans: ['IBM Plex Sans Thai', 'Prompt', 'sans-serif'],
        display: ['Prompt', 'sans-serif'],
        sarabun: ['Sarabun', 'sans-serif'],
        montserrat: ['Montserrat', 'sans-serif']
      },
      animation: {
        'pop': 'pop 0.25s cubic-bezier(0.4, 0, 0.2, 1)',
        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.2, 1) infinite',
        'spin-slow': 'spin 8s linear infinite'
      },
      keyframes: {
        pop: {
          '0%': { transform: 'scale(0.95)', opacity: '0' },
          '100%': { transform: 'scale(1)', opacity: '1' }
        }
      }
    }
  }
}
</script>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Thai:wght@300;400;500;600;700&family=Prompt:wght@300;400;500;600;700;800&family=Sarabun:wght@300;400;500;600;700&family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
body {
  min-height: 100vh;
  background-size: 400% 400%;
}
.day-card {
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
}
.day-card:hover:not(.empty-card) {
  transform: translateY(-5px);
  z-index: 20;
  box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
}
.empty-card {
  opacity: 0.35;
  pointer-events: none;
  background: rgba(243, 244, 246, 0.3) !important;
  border: 1px dashed rgba(156, 163, 175, 0.4) !important;
}
.glass {
  background: rgba(255, 255, 255, 0.82);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.45);
}
.theme-dark {
  background: linear-gradient(135deg, #0f172a, #1e1b4b, #111827) !important;
  color: #f8fafc;
}
.theme-dark .glass {
  background: rgba(15, 23, 42, 0.75);
  border: 1px solid rgba(255, 255, 255, 0.08);
  color: #f1f5f9;
}
.theme-sakura { background: linear-gradient(135deg, #fce7f3, #fbcfe8, #ffe4e6) !important; }
.theme-ocean { background: linear-gradient(135deg, #e0f2fe, #bae6fd, #cffafe) !important; }
.theme-forest { background: linear-gradient(135deg, #dcfce7, #bbf7d0, #f0fdf4) !important; }
.theme-sunset { background: linear-gradient(135deg, #ffedd5, #fed7aa, #ffeeaa) !important; }
.scrollbar-hidden::-webkit-scrollbar { width: 0px; height: 0px; }
.scrollbar-thin::-webkit-scrollbar { width: 6px; height: 6px; }
.scrollbar-thin::-webkit-scrollbar-track { background: rgba(0,0,0,0.05); border-radius: 10px; }
.scrollbar-thin::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.2); border-radius: 10px; }
@media print {
  body { background: white !important; color: black !important; }
  .no-print { display: none !important; }
  .glass { background: white !important; border: 1px solid #ccc !important; backdrop-filter: none !important; }
}
</style>
</head>
<body id="applicationBody" class="text-gray-800 transition-all duration-500 font-sans bg-slate-50">

<canvas id="particleCanvas" class="fixed inset-0 pointer-events-none z-50"></canvas>

<div class="container mx-auto px-2 py-6 max-w-7xl">

  <header class="glass rounded-3xl p-6 mb-6 shadow-xl relative overflow-hidden">
    <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-bl from-purple-300/20 to-pink-300/20 rounded-full blur-3xl -z-10 animate-pulse-slow"></div>
    
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
      <div>
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-purple-600 to-pink-500 flex items-center justify-center text-white text-xl font-bold shadow-lg shadow-purple-500/30 font-display animate-bounce">OS</div>
          <div>
            <h1 class="text-2xl md:text-3xl font-extrabold font-display bg-gradient-to-r from-purple-600 via-indigo-500 to-pink-500 bg-clip-text text-transparent">ปฏิทินของฉัน</h1>
            <p id="systemQuoteDisplay" class="text-gray-400 text-xs mt-0.5 italic font-medium"></p>
          </div>
        </div>
      </div>
      
      <div class="flex flex-wrap gap-2 items-center no-print">
        <div class="bg-gray-100/80 p-1.5 rounded-xl border border-gray-200/50 flex gap-1 shadow-inner backdrop-blur-sm">
          <button onclick="setGlobalViewMode('year')" id="viewBtn-year" class="px-3 py-1.5 text-xs font-bold rounded-lg transition-all">รายปี</button>
          <button onclick="setGlobalViewMode('month')" id="viewBtn-month" class="px-3 py-1.5 text-xs font-bold rounded-lg transition-all bg-white shadow-sm">รายเดือน</button>
          <button onclick="setGlobalViewMode('week')" id="viewBtn-week" class="px-3 py-1.5 text-xs font-bold rounded-lg transition-all">รายสัปดาห์</button>
          <button onclick="setGlobalViewMode('list')" id="viewBtn-list" class="px-3 py-1.5 text-xs font-bold rounded-lg transition-all">ไทม์ไลน์</button>
        </div>

        <select id="themeSelector" onchange="executeThemeChange(this.value)" class="px-2.5 py-1.5 rounded-xl border border-gray-200 bg-white text-xs font-bold focus:ring-2 focus:ring-purple-400 focus:outline-none shadow-sm cursor-pointer">
          <option value="theme-default">🎨 ธีมพื้นฐาน</option>
          <option value="theme-sakura">🌸 ซากุระชมพู</option>
          <option value="theme-ocean">🌊 มหาสมุทรฟ้า</option>
          <option value="theme-forest">🌲 ป่าไม้มรกต</option>
          <option value="theme-sunset">🌇 พระอาทิตย์ตก</option>
          <option value="theme-dark">🌙 กลางคืนรัตติกาล</option>
        </select>

        <button onclick="triggerDataExport()" class="px-3 py-1.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white text-xs font-bold transition-all shadow-md">ส่งออกคลาวด์</button>
        <label class="px-3 py-1.5 rounded-xl bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white text-xs font-bold transition-all shadow-md cursor-pointer text-center">
          นำเข้าคลาวด์
          <input type="file" id="cloudImportInput" accept=".json" class="hidden" onchange="triggerDataImport(event)">
        </label>
        <button onclick="executeAnonymizedExport()" class="px-2.5 py-1.5 rounded-xl bg-gray-600 hover:bg-gray-700 text-white text-xs font-bold transition-all shadow-sm">ส่งออกนิรนาม</button>
        <button onclick="purgeEntireDatabase()" class="px-3 py-1.5 rounded-xl bg-gradient-to-r from-red-500 to-pink-600 hover:from-red-600 hover:to-pink-700 text-white text-xs font-bold transition-all shadow-md">ล้างระบบทั้งปี</button>
      </div>
    </div>

    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4 border-t border-gray-200/60 pt-4 items-center">
      <div class="flex items-center gap-3 no-print">
        <button onclick="shiftTemporalMonth(-1)" class="w-9 h-9 flex items-center justify-center rounded-xl bg-white border border-gray-200 hover:bg-purple-50 text-gray-700 transition-all font-black shadow-sm">&larr;</button>
        <div class="min-w-[140px] text-center">
          <h2 id="calendarMonthYearHeading" class="text-lg font-black font-display text-gray-800"></h2>
        </div>
        <button onclick="shiftTemporalMonth(1)" class="w-9 h-9 flex items-center justify-center rounded-xl bg-white border border-gray-200 hover:bg-purple-50 text-gray-700 transition-all font-black shadow-sm">&rarr;</button>
        <button onclick="synchronizeToCurrentTime()" class="px-2.5 py-1.5 text-[11px] font-extrabold bg-purple-100 text-purple-700 rounded-lg hover:bg-purple-200 transition-all shadow-sm">วันนี้</button>
      </div>

      <div class="flex flex-wrap gap-3 justify-start md:justify-center items-center no-print">
        <div class="flex items-center gap-1.5">
          <span class="text-xs font-bold text-gray-400">ขนาด:</span>
          <select id="uiCardSizeSelector" onchange="triggerCardSizeAdjustment(this.value)" class="px-2 py-1 rounded-lg border border-gray-200 bg-white text-xs font-bold shadow-sm">
            <option value="small">เล็กกระชับ</option>
            <option value="medium" selected>ปานกลาง</option>
            <option value="large">ขยายใหญ่</option>
          </select>
        </div>
        <div class="flex items-center gap-1.5">
          <span class="text-xs font-bold text-gray-400">อักษร:</span>
          <select id="globalFontSelector" onchange="triggerFontFamilySwitch(this.value)" class="px-2 py-1 rounded-lg border border-gray-200 bg-white text-xs font-bold shadow-sm">
            <option value="font-sans">IBM Plex Thai</option>
            <option value="font-display">Prompt</option>
            <option value="font-sarabun">Sarabun</option>
            <option value="font-montserrat">Montserrat</option>
          </select>
        </div>
        <div class="flex items-center gap-2 bg-white px-2.5 py-1 rounded-lg border border-gray-200 shadow-sm">
          <span class="text-xs font-bold text-gray-400">เสียง:</span>
          <button onclick="toggleAudioMuteState()" id="audioMuteToggleButton" class="text-xs font-black text-purple-600 focus:outline-none">เปิด</button>
        </div>
      </div>

      <div class="flex items-center gap-2 no-print relative">
        <div class="absolute left-3 text-gray-400 pointer-events-none text-xs">🔍</div>
        <input type="text" id="globalSearchInput" oninput="executeGlobalDatabaseSearch(this.value)" placeholder="ค้นหางาน, บันทึก, หมวดหมู่ ทั่วทั้งเดือน..." class="w-full pl-8 pr-3 py-1.5 rounded-xl border border-gray-200 text-xs font-medium focus:ring-2 focus:ring-purple-400 focus:outline-none shadow-sm">
      </div>
    </div>

    <div class="mt-4 flex flex-wrap gap-2 items-center bg-purple-50/50 p-2.5 rounded-2xl border border-purple-100/50 no-print">
      <div id="pomodoroControlPanel" class="flex items-center gap-2 w-full justify-between flex-wrap">
        <div class="flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-full bg-red-500 animate-ping"></span>
          <span class="text-xs font-black text-purple-900 font-display">POMODORO FOCUS ENGINE:</span>
          <span id="pomodoroTimeDisplay" class="text-sm font-black text-red-600 font-mono tracking-widest bg-white px-2 py-0.5 rounded-md shadow-sm border border-red-100">25:00</span>
        </div>
        <div class="flex gap-1">
          <button onclick="executePomodoroCommand('start')" class="px-2 py-1 text-[10px] font-bold bg-red-500 text-white rounded-md hover:bg-red-600 transition-all shadow-sm">เริ่ม</button>
          <button onclick="executePomodoroCommand('pause')" class="px-2 py-1 text-[10px] font-bold bg-amber-500 text-white rounded-md hover:bg-amber-600 transition-all shadow-sm">พัก</button>
          <button onclick="executePomodoroCommand('reset')" class="px-2 py-1 text-[10px] font-bold bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-all shadow-sm">ล้าง</button>
          <button onclick="setPomodoroInterval(25)" class="px-1.5 py-1 text-[10px] font-bold bg-white text-gray-700 border rounded-md hover:bg-purple-50 transition-all">25ม.</button>
          <button onclick="setPomodoroInterval(5)" class="px-1.5 py-1 text-[10px] font-bold bg-white text-gray-700 border rounded-md hover:bg-purple-50 transition-all">5ม.</button>
          <button onclick="setPomodoroInterval(15)" class="px-1.5 py-1 text-[10px] font-bold bg-white text-gray-700 border rounded-md hover:bg-purple-50 transition-all">15ม.</button>
        </div>
      </div>
    </div>

    <div class="mt-3 flex flex-wrap gap-2 items-center no-print">
      <span class="text-xs font-bold text-gray-400">ตัวสร้างสีพื้นหลัง:</span>
      <input type="color" id="gradientColorPickerOne" value="#fef3f2" oninput="compileCustomBackgroundGradient()" class="w-6 h-6 rounded cursor-pointer border border-gray-300">
      <input type="color" id="gradientColorPickerTwo" value="#faf5ff" oninput="compileCustomBackgroundGradient()" class="w-6 h-6 rounded cursor-pointer border border-gray-300">
      <select id="gradientAngleSelector" onchange="compileCustomBackgroundGradient()" class="px-1.5 py-0.5 text-[10px] font-bold border rounded-md bg-white">
        <option value="-45deg">-45°</option>
        <option value="135deg">135°</option>
        <option value="90deg">90°</option>
        <option value="180deg">180°</option>
      </select>
      <div id="realtimeDatabaseSyncIndicator" class="ml-auto flex items-center gap-1.5 text-[10px] font-bold text-emerald-600">
        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block animate-pulse"></span>
        ระบบซิงค์เสถียร
      </div>
    </div>
  </header>

  <div id="calendarMainWorkspaceGrid" class="transition-all duration-300"></div>

  <div class="glass rounded-3xl p-6 mt-6 shadow-xl relative overflow-hidden">
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-4 border-b border-gray-100 pb-3 gap-2">
      <h2 class="text-xl font-black font-display flex items-center gap-2 text-gray-800">
        <span class="text-2xl animate-spin-slow">📊</span> คลังสถิติและผลประเมินผลลัพธ์รายเดือน คอร์ปอเรชัน
      </h2>
      <div class="flex items-center gap-2">
        <span class="text-xs font-bold text-gray-400">ประสิทธิภาพรวม:</span>
        <span id="monthlyProductivityGradeBadge" class="px-3 py-1 rounded-xl bg-purple-600 text-white font-black text-xs font-display shadow-md shadow-purple-500/20">GRADE -</span>
      </div>
    </div>
    
    <div class="grid grid-cols-2 lg:grid-cols-6 gap-3">
      <div class="bg-gradient-to-br from-pink-500/10 to-rose-500/5 rounded-2xl p-4 text-center border border-pink-100/50 shadow-sm">
        <div class="text-3xl font-black text-pink-600 font-display" id="statDaysWithEvents">0</div>
        <div class="text-[11px] text-pink-700 font-bold mt-1">วันที่มีกิจกรรมบันทึก</div>
      </div>
      <div class="bg-gradient-to-br from-purple-500/10 to-indigo-500/5 rounded-2xl p-4 text-center border border-purple-100/50 shadow-sm">
        <div class="text-3xl font-black text-purple-600 font-display" id="statTotalTasksCount">0</div>
        <div class="text-[11px] text-purple-700 font-bold mt-1">จำนวนงานทั้งหมด</div>
      </div>
      <div class="bg-gradient-to-br from-emerald-500/10 to-teal-500/5 rounded-2xl p-4 text-center border border-emerald-100/50 shadow-sm">
        <div class="text-3xl font-black text-emerald-600 font-display" id="statCompletedTasksCount">0</div>
        <div class="text-[11px] text-emerald-700 font-bold mt-1">ภารกิจที่ทำเสร็จสิ้น</div>
      </div>
      <div class="bg-gradient-to-br from-amber-500/10 to-orange-500/5 rounded-2xl p-4 text-center border border-amber-100/50 shadow-sm">
        <div class="text-3xl font-black text-amber-600 font-display" id="statLongestHabitStreak">0</div>
        <div class="text-[11px] text-amber-700 font-bold mt-1">พฤติกรรมทำต่อเนื่องสูงสุด</div>
      </div>
      <div class="bg-gradient-to-br from-blue-500/10 to-cyan-500/5 rounded-2xl p-4 text-center border border-blue-100/50 shadow-sm">
        <div class="text-3xl font-black text-blue-600 font-display" id="statFinancialIncomeSum">0</div>
        <div class="text-[11px] text-blue-700 font-bold mt-1">ยอดรวมรายรับสุทธิ</div>
      </div>
      <div class="bg-gradient-to-br from-red-500/10 to-orange-500/5 rounded-2xl p-4 text-center border border-red-100/50 shadow-sm">
        <div class="text-3xl font-black text-red-600 font-display" id="statFinancialExpenseSum">0</div>
        <div class="text-[11px] text-red-700 font-bold mt-1">ยอดรวมรายจ่ายสุทธิ</div>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
      <div class="bg-white/60 rounded-2xl p-4 border border-gray-200/50 shadow-inner">
        <h3 class="text-xs font-black text-gray-500 uppercase tracking-wider mb-2">📊 เวกเตอร์การกระจายสภาวะอารมณ์ (%):</h3>
        <div id="moodAnalyticsDistributionReport" class="space-y-1.5 text-xs font-bold text-gray-700"></div>
      </div>
      <div class="bg-white/60 rounded-2xl p-4 border border-gray-200/50 shadow-inner">
        <h3 class="text-xs font-black text-gray-500 uppercase tracking-wider mb-2">💧 อัตราการรักษาสมดุลของน้ำและการเดิน:</h3>
        <div class="space-y-2 text-xs font-bold text-gray-700">
          <div class="flex justify-between"><span>ปริมาณน้ำดื่มสะสม:</span><span id="biometricTotalWaterMetric" class="text-blue-600">0 แก้ว</span></div>
          <div class="flex justify-between"><span>จำนวนก้าวเดินสะสม:</span><span id="biometricTotalStepsMetric" class="text-emerald-600">0 ก้าว</span></div>
          <div class="flex justify-between"><span>สมดุลแคลอรีสุทธิ:</span><span id="biometricNetCaloricMetric" class="text-purple-600">0 kcal</span></div>
        </div>
      </div>
      <div class="bg-white/60 rounded-2xl p-4 border border-gray-200/50 shadow-inner">
        <h3 class="text-xs font-black text-gray-500 uppercase tracking-wider mb-2">🎯 เป้าหมายเชิงยุทธศาสตร์ประจำเดือน:</h3>
        <textarea id="monthlyGlobalTargetObjectiveInput" oninput="saveMonthlyTargetObjective(this.value)" rows="3" placeholder="ระบุเป้าหมายหลักประจำเดือนนี้เพื่อกระตุ้นเตือนความจำระบบ..." class="w-full bg-transparent border-none text-xs font-medium focus:outline-none resize-none text-gray-700"></textarea>
      </div>
    </div>
  </div>

  <footer class="text-center text-gray-400 text-[11px] font-bold mt-6 pb-4">
    ระบบจัดการสภาวะแวดล้อมส่วนบุคคลเชิงเวลาสมบูรณ์แบบเวอร์ชัน 2.5 • จัดเก็บฐานข้อมูลเชิงโครงสร้างต้นไม้ภายในเว็บบราวเซอร์ของคุณอย่างปลอดภัย
  </footer>
</div>

<div id="dynamicExecutionModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-md hidden items-center justify-center z-50 p-2 md:p-4 opacity-0 transition-opacity duration-300">
  <div class="bg-white rounded-3xl shadow-2xl max-w-2xl w-full max-h-[92vh] overflow-hidden flex flex-col transform scale-95 transition-transform duration-300 border border-gray-100">
    <div class="bg-gradient-to-r from-purple-600 via-indigo-600 to-pink-500 p-5 text-white relative">
      <div class="flex items-center justify-between">
        <div>
          <div id="modalDateTimeContextLabel" class="text-xs opacity-75 font-bold tracking-wider"></div>
          <h3 id="modalCalendarDayHeading" class="text-2xl font-black font-display mt-0.5"></h3>
        </div>
        <button onclick="closeDynamicExecutionModal()" class="w-8 h-8 rounded-full bg-white/15 hover:bg-white/25 transition-all text-sm flex items-center justify-center font-bold">✕</button>
      </div>
    </div>

    <div class="p-5 overflow-y-auto scrollbar-thin flex-1 space-y-5 bg-slate-50/50">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white p-3.5 rounded-2xl border border-gray-200/60 shadow-sm">
          <label class="text-xs font-black text-gray-500 block mb-2 uppercase tracking-wide">🎨 กำหนดรหัสสีประจำวัน</label>
          <div id="modalColorPaletteContainer" class="flex flex-wrap gap-1.5"></div>
        </div>

        <div class="bg-white p-3.5 rounded-2xl border border-gray-200/60 shadow-sm">
          <label class="text-xs font-black text-gray-500 block mb-2 uppercase tracking-wide">🎭 ตัวติดตามดัชนีสภาวะอารมณ์</label>
          <div id="modalMoodTrackerContainer" class="flex gap-1"></div>
        </div>
      </div>

      <div class="bg-white p-3.5 rounded-2xl border border-gray-200/60 shadow-sm">
        <label class="text-xs font-black text-gray-500 block mb-1.5 uppercase tracking-wide">✨ คลังสติกเกอร์และสัญลักษณ์แสดงสถานะ</label>
        <div id="modalStickerPaletteContainer" class="flex flex-wrap gap-1 max-h-24 overflow-y-auto scrollbar-thin p-1.5 bg-slate-50 rounded-xl border"></div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <div class="bg-white p-3 rounded-2xl border border-gray-200/60 shadow-sm text-center">
          <label class="text-[11px] font-black text-gray-400 block mb-1.5 uppercase">💧 ตัววัดปริมาณน้ำดื่ม</label>
          <div id="modalWaterIntakeGlassContainer" class="flex justify-center gap-0.5"></div>
          <div id="modalWaterTextCounter" class="text-xs font-extrabold text-blue-600 mt-1.5">0 / 8 แก้ว</div>
        </div>

        <div class="bg-white p-3 rounded-2xl border border-gray-200/60 shadow-sm">
          <label class="text-[11px] font-black text-gray-400 block mb-1 uppercase">👟 จำนวนก้าวเดิน</label>
          <input type="number" id="modalPedometerStepInput" placeholder="ระบุจำนวนก้าว..." class="w-full text-center px-2 py-1 rounded-lg border text-xs font-bold focus:ring-2 focus:ring-purple-400 focus:outline-none">
        </div>

        <div class="bg-white p-3 rounded-2xl border border-gray-200/60 shadow-sm">
          <label class="text-[11px] font-black text-gray-400 block mb-1 uppercase">🔥 สมดุลพลังงานแคลอรี</label>
          <div class="flex gap-1">
            <input type="number" id="modalCalorieInInput" placeholder="รับเข้า" title="Calories In" class="w-1/2 text-center px-1 py-1 rounded-lg border text-xs font-bold focus:outline-none focus:ring-1 focus:ring-purple-400">
            <input type="number" id="modalCalorieOutInput" placeholder="เผาผลาญ" title="Calories Out" class="w-1/2 text-center px-1 py-1 rounded-lg border text-xs font-bold focus:outline-none focus:ring-1 focus:ring-purple-400">
          </div>
        </div>
      </div>

      <div class="bg-white p-4 rounded-2xl border border-gray-200/60 shadow-sm">
        <div class="flex items-center justify-between mb-2">
          <label class="text-xs font-black text-gray-500 uppercase tracking-wide">💰 บัญชีแยกประเภทรายรับ - รายจ่ายรายวัน</label>
          <button onclick="insertNewFinancialRow()" class="text-[10px] font-bold bg-indigo-50 text-indigo-600 border border-indigo-200 px-2 py-1 rounded-md hover:bg-indigo-100 transition-all">+ เพิ่มรายการธุรกรรม</button>
        </div>
        <div id="modalFinancialLedgerRowsContainer" class="space-y-1.5 max-h-32 overflow-y-auto scrollbar-thin"></div>
      </div>

      <div class="bg-white p-4 rounded-2xl border border-gray-200/60 shadow-sm">
        <label class="text-xs font-black text-gray-500 block mb-2 uppercase tracking-wide">📝 สมุดบันทึกและอนุทินส่วนบุคคลประจำวัน</label>
        <textarea id="modalDayNoteTextarea" rows="3" placeholder="เขียนรายละเอียดความคิด เหตุการณ์สำคัญ หรือสรุปสาระสำคัญของวันนี้..." class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-purple-400 text-xs font-medium resize-none text-gray-700 bg-slate-50/50"></textarea>
      </div>

      <div class="bg-white p-4 rounded-2xl border border-gray-200/60 shadow-sm">
        <div class="flex items-center justify-between mb-3">
          <label class="text-xs font-black text-gray-500 uppercase tracking-wide">🎯 รายการภารกิจและงานที่ต้องจัดวางโครงสร้าง</label>
          <button onclick="insertNewTaskRow()" class="text-xs bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white px-3 py-1 rounded-full transition-all shadow-sm font-bold">+ เพิ่มงานใหม่</button>
        </div>
        <div id="modalTaskListRowsContainer" class="space-y-2 max-h-48 overflow-y-auto scrollbar-thin"></div>
      </div>
    </div>

    <div class="p-4 bg-gray-50 border-t border-gray-100 flex gap-2 no-print">
      <button onclick="executeDayDataPurge()" class="px-4 py-2 rounded-xl bg-red-50 hover:bg-red-100 text-red-600 text-xs font-bold border border-red-200 transition-all">ล้างข้อมูลวันนี้</button>
      <button onclick="closeDynamicExecutionModal()" class="ml-auto px-6 py-2 rounded-xl bg-gradient-to-r from-purple-600 via-indigo-600 to-pink-500 hover:from-purple-700 hover:to-indigo-700 text-white text-xs font-bold transition-all shadow-md">บันทึกโครงสร้างข้อมูล</button>
    </div>
  </div>
</div>

<div id="globalToastNotificationPopup" class="fixed bottom-6 right-6 transform translate-y-20 opacity-0 pointer-events-none bg-slate-900/95 text-white px-5 py-3 rounded-2xl shadow-2xl text-xs font-bold z-50 transition-all duration-300 border border-white/10 flex items-center gap-2"></div>

<script>
const SYSTEM_QUOTES = [
  "วินัยคือสะพานเชื่อมระหว่างเป้าหมายและความสำเร็จสูงสุด",
  "สิ่งที่ทำเป็นประจำทุกวันมีความสำคัญมากกว่าสิ่งที่ทำเพียงครั้งเดียว",
  "เวลาที่บริหารอย่างมีกลยุทธ์ คือทรัพยากรที่มีค่าที่สุดในโลก",
  "จงพัฒนาตนเองวันละหนึ่งเปอร์เซ็นต์อย่างไม่หยุดยั้ง",
  "ความสำเร็จไม่ใช่เรื่องบังเอิญ แต่เกิดจากการวางแผนที่เป็นระบบ",
  "จงจดจ่ออยู่กับกระบวนการ แล้วผลลัพธ์จะดูแลตัวมันเอง"
];

const STICKERS = ['🌟','💕','🎉','🎂','🎁','🍔','☕','✈️','🏖️','📚','💪','🛒','💰','🎯','🏆','⭐','🌙','☀️','🌈','🎮','🎵','🎬','🏥','🚗','🏠','📞','💌','🧹','💤','🧁','🩺','🥑','🏋️','🔑','💡','🛡️','📈','🧭','📌','🔔'];
const MONTH_NAMES_THAI = ["มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"];
const THAI_PUBLIC_HOLIDAYS = {
  "1-0": "วันขึ้นปีใหม่", "6-3": "วันจักรี", "13-3": "วันสงกรานต์", "14-3": "วันสงกรานต์", "15-3": "วันสงกรานต์",
  "1-4": "วันแรงงานแห่งชาติ", "4-4": "วันฉัตรมงคล", "28-6": "วันเฉลิมพระชนมพรรษา ร.10", "12-7": "วันแม่แห่งชาติ",
  "13-9": "วันคล้ายวันสวรรคต ร.9", "23-9": "วันปิยมหาราช", "5-11": "วันพ่อแห่งชาติ", "10-11": "วันรัฐธรรมนูญ", "31-11": "วันสิ้นปี"
};

let currentTemporalDate = new Date();
let currentYearScope = currentTemporalDate.getFullYear();
let currentMonthScope = currentTemporalDate.getMonth();

let globalDatabaseState = JSON.parse(localStorage.getItem('hyper_faceted_calendar_db') || '{}');
let globalSettingsState = JSON.parse(localStorage.getItem('hyper_faceted_calendar_settings') || '{}');
let activeTargetDayIndex = null;
let pomodoroTimerCountdownReference = null;
let pomodoroRemainingSeconds = 1500;
let globalSearchQueryCache = "";

function saveDatabaseToLocalStorage() {
  localStorage.setItem('hyper_faceted_calendar_db', JSON.stringify(globalDatabaseState));
  sessionStorage.setItem('hyper_faceted_calendar_backup', JSON.stringify(globalDatabaseState));
}

function saveSettingsToLocalStorage() {
  localStorage.setItem('hyper_faceted_calendar_settings', JSON.stringify(globalSettingsState));
}

function getDayRecordInstance(year, month, day) {
  const compositeKey = `${year}-${month}`;
  if (!globalDatabaseState[compositeKey]) globalDatabaseState[compositeKey] = {};
  if (!globalDatabaseState[compositeKey][day]) {
    globalDatabaseState[compositeKey][day] = {
      color: '', note: '', tasks: [], stickers: [], mood: '', water: 0, steps: 0, calorieIn: 0, calorieOut: 0, finance: []
    };
  }
  return globalDatabaseState[compositeKey][day];
}

function computeTargetMonthCompositeKey() {
  return `${currentYearScope}-${currentMonthScope}`;
}

function synchronizeToCurrentTime() {
  const temporaryRealDate = new Date();
  currentYearScope = temporaryRealDate.getFullYear();
  currentMonthScope = temporaryRealDate.getMonth();
  triggerAudioSynthesisCue('click');
  renderCalendarMainWorkspace();
}

function shiftTemporalMonth(directionOffset) {
  currentMonthScope += directionOffset;
  if (currentMonthScope > 11) {
    currentMonthScope = 0;
    currentYearScope++;
  } else if (currentMonthScope < 0) {
    currentMonthScope = 11;
    currentYearScope--;
  }
  triggerAudioSynthesisCue('click');
  renderCalendarMainWorkspace();
}

function setGlobalViewMode(targetMode) {
  globalSettingsState.viewMode = targetMode;
  saveSettingsToLocalStorage();
  const modes = ['year', 'month', 'week', 'list'];
  modes.forEach(m => {
    const btn = document.getElementById(`viewBtn-${m}`);
    if (btn) {
      btn.classList.remove('bg-white', 'shadow-sm', 'text-purple-600');
      if (m === targetMode) btn.classList.add('bg-white', 'shadow-sm', 'text-purple-600');
    }
  });
  triggerAudioSynthesisCue('click');
  renderCalendarMainWorkspace();
}

function renderCalendarMainWorkspace() {
  const workspaceContainer = document.getElementById('calendarMainWorkspaceGrid');
  const activeMode = globalSettingsState.viewMode || 'month';
  document.getElementById('calendarMonthYearHeading').textContent = `${MONTH_NAMES_THAI[currentMonthScope]} ${currentYearScope}`;
  
  if (activeMode === 'month') {
    compileMonthGridView(workspaceContainer);
  } else if (activeMode === 'year') {
    compileYearGridView(workspaceContainer);
  } else if (activeMode === 'week') {
    compileWeekTimelineView(workspaceContainer);
  } else if (activeMode === 'list') {
    compileLinearChronologicalListView(workspaceContainer);
  }
  executeAnalyticsEngineCalculation();
  applyDynamicStylingOverrides();
}

function compileMonthGridView(container) {
  const firstDayDayOfWeekOffset = new Date(currentYearScope, currentMonthScope, 1).getDay();
  const totalDaysInCurrentMonth = new Date(currentYearScope, currentMonthScope + 1, 0).getDate();
  const currentCardSize = globalSettingsState.cardSize || 'medium';
  
  let layoutHeightClass = 'min-h-[115px]';
  if (currentCardSize === 'small') layoutHeightClass = 'min-h-[85px]';
  if (currentCardSize === 'large') layoutHeightClass = 'min-h-[155px]';

  let rawHtmlGridAccumulator = `<div class="grid grid-cols-7 gap-2 md:gap-3 animate-pop">`;

  for (let spaceIndex = 0; spaceIndex < firstDayDayOfWeekOffset; spaceIndex++) {
    rawHtmlGridAccumulator += `<div class="day-card empty-card rounded-2xl ${layoutHeightClass}"></div>`;
  }

  for (let calendarDayLoopIndex = 1; calendarDayLoopIndex <= totalDaysInCurrentMonth; calendarDayLoopIndex++) {
    const dayDataInstance = getDayRecordInstance(currentYearScope, currentMonthScope, calendarDayLoopIndex);
    const dayOfWeekIndex = new Date(currentYearScope, currentMonthScope, calendarDayLoopIndex).getDay();
    const isWeekendMarker = (dayOfWeekIndex === 0 || dayOfWeekIndex === 6);
    
    const absoluteHolidayLabel = THAI_PUBLIC_HOLIDAYS[`${calendarDayLoopIndex}-${currentMonthScope}`] || '';
    
    let simulatedLunarSymbol = '🌕';
    if (calendarDayLoopIndex % 29 < 7) simulatedLunarSymbol = '🌑';
    else if (calendarDayLoopIndex % 29 < 14) simulatedLunarSymbol = '🌓';
    else if (calendarDayLoopIndex % 29 < 21) simulatedLunarSymbol = '🌕';
    else simulatedLunarSymbol = '🌗';

    let compoundStyleString = dayDataInstance.color ? `background:${dayDataInstance.color};` : 'background:#ffffff;';
    let darkTextOverrideClass = (dayDataInstance.color === '#1f2937') ? 'text-white' : 'text-gray-800';
    let weekendColorTextClass = isWeekendMarker ? 'text-red-500' : 'text-gray-400';
    if (dayDataInstance.color === '#1f2937') weekendColorTextClass = isWeekendMarker ? 'text-red-300' : 'text-gray-300';

    if (globalSearchQueryCache && !evaluateSearchMatchInDayData(dayDataInstance, calendarDayLoopIndex)) {
      rawHtmlGridAccumulator += '';
      continue;
    }

    const tasksCompletedCount = dayDataInstance.tasks.filter(t => t.done).length;
    const tasksTotalCount = dayDataInstance.tasks.length;

    rawHtmlGridAccumulator += `
      <div onclick="openDynamicExecutionModal(${calendarDayLoopIndex})" class="day-card ${layoutHeightClass} ${darkTextOverrideClass} rounded-2xl p-3 cursor-pointer border border-slate-200/60 shadow-sm flex flex-col justify-between" style="${compoundStyleString}">
        <div class="flex items-start justify-between">
          <div class="flex flex-col">
            <span class="day-number text-lg font-black font-display leading-none">${calendarDayLoopIndex}</span>
            ${absoluteHolidayLabel ? `<span class="text-[9px] text-red-500 font-bold tracking-tight bg-red-50 px-1 rounded mt-0.5 max-w-[65px] truncate">${absoluteHolidayLabel}</span>` : ''}
          </div>
          <div class="flex gap-0.5 items-center max-w-[60px] overflow-hidden">
            <span class="text-[10px] opacity-40 filter grayscale">${simulatedLunarSymbol}</span>
            ${(dayDataInstance.stickers || []).slice(0, 2).map(st => `<span class="text-sm">${st}</span>`).join('')}
          </div>
        </div>
        
        ${dayDataInstance.note ? `<p class="text-[10px] opacity-75 font-medium line-clamp-2 mt-1 leading-normal">${escapeHtmlSpecialChars(dayDataInstance.note)}</p>` : '<div class="flex-1"></div>'}
        
        <div class="flex items-center justify-between mt-1.5 pt-1 border-t border-black/5">
          <div class="text-[10px] font-black">${dayDataInstance.mood || ''}</div>
          ${tasksTotalCount > 0 ? `<div class="text-[9px] font-bold px-1.5 py-0.5 bg-black/5 rounded-full">${tasksCompletedCount}/${tasksTotalCount}</div>` : ''}
        </div>
      </div>
    `;
  }

  rawHtmlGridAccumulator += `</div>`;
  container.innerHTML = rawHtmlGridAccumulator;
}

function compileYearGridView(container) {
  let yearHtmlAccumulator = `<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 animate-pop">`;
  
  for (let monthMatrixIndex = 0; monthMatrixIndex < 12; monthMatrixIndex++) {
    const totalDaysInMonth = new Date(currentYearScope, monthMatrixIndex + 1, 0).getDate();
    let computedEventsInMonth = 0;
    const targetKey = `${currentYearScope}-${monthMatrixIndex}`;
    
    if (globalDatabaseState[targetKey]) {
      Object.keys(globalDatabaseState[targetKey]).forEach(d => {
        const item = globalDatabaseState[targetKey][d];
        if (item.note || item.tasks.length > 0 || item.stickers.length > 0) computedEventsInMonth++;
      });
    }

    yearHtmlAccumulator += `
      <div onclick="currentMonthScope=${monthMatrixIndex}; setGlobalViewMode('month');" class="bg-white p-4 rounded-3xl border border-gray-200/70 shadow-sm cursor-pointer hover:border-purple-400 transition-all flex flex-col justify-between">
        <h3 class="text-sm font-black font-display text-gray-800">${MONTH_NAMES_THAI[monthMatrixIndex]}</h3>
        <div class="mt-4 flex items-center justify-between">
          <span class="text-xs font-bold text-gray-400">${totalDaysInMonth} วันในระบบ</span>
          <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-purple-100 text-purple-700">${computedEventsInMonth} วันมีบันทึก</span>
        </div>
      </div>
    `;
  }
  
  yearHtmlAccumulator += `</div>`;
  container.innerHTML = yearHtmlAccumulator;
}

function compileWeekTimelineView(container) {
  let weekHtmlAccumulator = `<div class="flex flex-col gap-3 animate-pop">`;
  const referenceDayIndex = currentTemporalDate.getDate();
  const currentDayOfWeek = currentTemporalDate.getDay();
  
  for (let weekOffsetIndex = -currentDayOfWeek; weekOffsetIndex < 7 - currentDayOfWeek; weekOffsetIndex++) {
    const projectedTargetDate = new Date(currentYearScope, currentMonthScope, referenceDayIndex + weekOffsetIndex);
    if (projectedTargetDate.getMonth() !== currentMonthScope) continue;
    
    const numericDay = projectedTargetDate.getDate();
    const targetDayDataInstance = getDayRecordInstance(currentYearScope, currentMonthScope, numericDay);
    
    weekHtmlAccumulator += `
      <div onclick="openDynamicExecutionModal(${numericDay})" class="bg-white p-4 rounded-2xl border border-gray-200 shadow-sm cursor-pointer hover:bg-slate-50 transition-all flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-purple-600 text-white flex items-center justify-center font-black text-sm font-display">${numericDay}</div>
          <div>
            <h4 class="text-xs font-bold text-gray-400">บันทึกประจำวันและอนุทินวิเคราะห์</h4>
            <p class="text-xs font-medium text-gray-700 mt-0.5 truncate max-w-md">${targetDayDataInstance.note || 'ไม่มีคำอธิบายเพิ่มเติมสำหรับวันนี้'}</p>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <span class="text-xs font-bold px-2.5 py-1 rounded-lg bg-slate-100 text-gray-600">🎯 งาน: ${targetDayDataInstance.tasks.length} รายการ</span>
          <span class="text-xs font-bold px-2.5 py-1 rounded-lg bg-blue-50 text-blue-600">💧 ดื่มน้ำ: ${targetDayDataInstance.water || 0} แก้ว</span>
        </div>
      </div>
    `;
  }
  
  weekHtmlAccumulator += `</div>`;
  container.innerHTML = weekHtmlAccumulator;
}

function compileLinearChronologicalListView(container) {
  const maximumDaysInActiveMonth = new Date(currentYearScope, currentMonthScope + 1, 0).getDate();
  let listHtmlAccumulator = `<div class="space-y-2 max-h-[60vh] overflow-y-auto scrollbar-thin pr-1 animate-pop">`;
  let activeRecordsFoundInMonth = false;

  for (let chronologicalDayIndex = 1; chronologicalDayIndex <= maximumDaysInActiveMonth; chronologicalDayIndex++) {
    const historicalDayInstance = getDayRecordInstance(currentYearScope, currentMonthScope, chronologicalDayIndex);
    if (!historicalDayInstance.note && historicalDayInstance.tasks.length === 0 && historicalDayInstance.stickers.length === 0) continue;
    
    activeRecordsFoundInMonth = true;
    listHtmlAccumulator += `
      <div onclick="openDynamicExecutionModal(${chronologicalDayIndex})" class="bg-white p-3 rounded-xl border border-gray-100 shadow-sm cursor-pointer hover:border-purple-300 transition-all flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <span class="text-xs font-black font-mono text-purple-600 bg-purple-50 px-2 py-1 rounded-lg">วันที่ ${chronologicalDayIndex.toString().padStart(2, '0')}</span>
          <span class="text-xs font-medium text-gray-600 truncate max-w-xs md:max-w-md">${escapeHtmlSpecialChars(historicalDayInstance.note) || 'มีภารกิจแต่ไม่มีข้อความอนุทิน'}</span>
        </div>
        <div class="text-[11px] font-black text-gray-400">${historicalDayInstance.tasks.filter(t => t.done).length}/${historicalDayInstance.tasks.length} สำเร็จ</div>
      </div>
    `;
  }
  
  if (!activeRecordsFoundInMonth) {
    listHtmlAccumulator += `<div class="text-center text-gray-400 text-xs py-12 font-bold bg-white rounded-2xl border border-dashed">ไม่มีกิจกรรมหลักที่บันทึกไว้ในโมดูลรายชื่อประจำเดือนนี้</div>`;
  }
  
  listHtmlAccumulator += `</div>`;
  container.innerHTML = listHtmlAccumulator;
}

function evaluateSearchMatchInDayData(dataObject, currentDayNumber) {
  const sanitizedQuery = globalSearchQueryCache.toLowerCase().trim();
  if (currentDayNumber.toString() === sanitizedQuery) return true;
  if (dataObject.note && dataObject.note.toLowerCase().includes(sanitizedQuery)) return true;
  if (dataObject.mood && dataObject.mood.toLowerCase().includes(sanitizedQuery)) return true;
  
  let subTaskFoundFlag = false;
  dataObject.tasks.forEach(t => {
    if (t.text && t.text.toLowerCase().includes(sanitizedQuery)) subTaskFoundFlag = true;
    if (t.category && t.category.toLowerCase().includes(sanitizedQuery)) subTaskFoundFlag = true;
  });
  return subTaskFoundFlag;
}

function executeGlobalDatabaseSearch(inputQueryValue) {
  globalSearchQueryCache = inputQueryValue;
  renderCalendarMainWorkspace();
}

function executeAnalyticsEngineCalculation() {
  let accumulatedDaysWithRecordsCount = 0;
  let totalTasksAggregatedCount = 0;
  let completedTasksAggregatedCount = 0;
  let totalWaterGlassesDrankCount = 0;
  let totalStepsWalkedCount = 0;
  let totalCalorieInValue = 0;
  let totalCalorieOutValue = 0;
  let totalFinancialIncomeSumValue = 0;
  let totalFinancialExpenseSumValue = 0;
  let maximalContinuousStreakDays = 0;
  let currentActiveStreakSequence = 0;
  
  const moodFrequencyMapObject = { '🌟 ยอดเยี่ยม': 0, '😊 มีความสุข': 0, '😐 ทรงตัว': 0, '😥 เครียด': 0, '🔋 เหนื่อยล้า': 0 };
  const totalDaysInActiveMonthScope = new Date(currentYearScope, currentMonthScope + 1, 0).getDate();

  for (let analysisDayLoopIndex = 1; analysisDayLoopIndex <= totalDaysInActiveMonthScope; analysisDayLoopIndex++) {
    const iterationDayInstance = getDayRecordInstance(currentYearScope, currentMonthScope, analysisDayLoopIndex);
    
    let logicalHasRecordFlag = false;
    if (iterationDayInstance.note || iterationDayInstance.tasks.length > 0 || iterationDayInstance.stickers.length > 0 || iterationDayInstance.water > 0 || iterationDayInstance.steps > 0 || iterationDayInstance.finance.length > 0) {
      logicalHasRecordFlag = true;
      accumulatedDaysWithRecordsCount++;
    }

    if (logicalHasRecordFlag) {
      currentActiveStreakSequence++;
      if (currentActiveStreakSequence > maximalContinuousStreakDays) maximalContinuousStreakDays = currentActiveStreakSequence;
    } else {
      currentActiveStreakSequence = 0;
    }

    totalTasksAggregatedCount += iterationDayInstance.tasks.length;
    completedTasksAggregatedCount += iterationDayInstance.tasks.filter(t => t.done).length;
    totalWaterGlassesDrankCount += (iterationDayInstance.water || 0);
    totalStepsWalkedCount += (iterationDayInstance.steps || 0);
    totalCalorieInValue += (iterationDayInstance.calorieIn || 0);
    totalCalorieOutValue += (iterationDayInstance.calorieOut || 0);

    if (iterationDayInstance.mood && moodFrequencyMapObject[iterationDayInstance.mood] !== undefined) {
      moodFrequencyMapObject[iterationDayInstance.mood]++;
    }

    (iterationDayInstance.finance || []).forEach(tx => {
      const parsedValue = parseFloat(tx.amount) || 0;
      if (tx.type === 'income') totalFinancialIncomeSumValue += parsedValue;
      if (tx.type === 'expense') totalFinancialExpenseSumValue += parsedValue;
    });
  }

  document.getElementById('statDaysWithEvents').textContent = accumulatedDaysWithRecordsCount;
  document.getElementById('statTotalTasksCount').textContent = totalTasksAggregatedCount;
  document.getElementById('statCompletedTasksCount').textContent = completedTasksAggregatedCount;
  document.getElementById('statLongestHabitStreak').textContent = maximalContinuousStreakDays;
  document.getElementById('statFinancialIncomeSum').textContent = totalFinancialIncomeSumValue.toLocaleString() + ' ฿';
  document.getElementById('statFinancialExpenseSum').textContent = totalFinancialExpenseSumValue.toLocaleString() + ' ฿';

  document.getElementById('biometricTotalWaterMetric').textContent = totalWaterGlassesDrankCount + ' แก้ว';
  document.getElementById('biometricTotalStepsMetric').textContent = totalStepsWalkedCount.toLocaleString() + ' ก้าว';
  document.getElementById('biometricNetCaloricMetric').textContent = (totalCalorieInValue - totalCalorieOutValue) + ' kcal';

  const analyticsReportContainer = document.getElementById('moodAnalyticsDistributionReport');
  let moodHtmlAccumulator = '';
  Object.keys(moodFrequencyMapObject).forEach(moodKey => {
    const rawCount = moodFrequencyMapObject[moodKey];
    const calculatedPercentage = accumulatedDaysWithRecordsCount > 0 ? Math.round((rawCount / accumulatedDaysWithRecordsCount) * 100) : 0;
    moodHtmlAccumulator += `
      <div class="flex items-center justify-between">
        <span>${moodKey}:</span>
        <span class="text-purple-600">${calculatedPercentage}% (${rawCount} วัน)</span>
      </div>
    `;
  });
  analyticsReportContainer.innerHTML = moodHtmlAccumulator;

  const targetGradeBadge = document.getElementById('monthlyProductivityGradeBadge');
  let evaluationScore = 0;
  if (totalTasksAggregatedCount > 0) evaluationScore += (completedTasksAggregatedCount / totalTasksAggregatedCount) * 60;
  if (totalWaterGlassesDrankCount > 10) evaluationScore += 20;
  if (maximalContinuousStreakDays > 3) evaluationScore += 20;

  let performanceGradeCharacter = 'F';
  if (evaluationScore >= 85) performanceGradeCharacter = 'GRADE A';
  else if (evaluationScore >= 70) performanceGradeCharacter = 'GRADE B';
  else if (evaluationScore >= 50) performanceGradeCharacter = 'GRADE C';
  else if (evaluationScore >= 30) performanceGradeCharacter = 'GRADE D';
  
  if (totalTasksAggregatedCount === 0 && accumulatedDaysWithRecordsCount === 0) performanceGradeCharacter = 'NO DATA';
  targetGradeBadge.textContent = performanceGradeCharacter;
}

function openDynamicExecutionModal(dayIndex) {
  activeTargetDayIndex = dayIndex;
  const targetDayDataInstance = getDayRecordInstance(currentYearScope, currentMonthScope, dayIndex);
  
  const targetDayOfWeekNameIndex = new Date(currentYearScope, currentMonthScope, dayIndex).getDay();
  const weekDayNamesThaiArray = ["อาทิตย์", "จันทร์", "อังคาร", "พุธ", "พฤหัสบดี", "ศุกร์", "เสาร์"];
  
  document.getElementById('modalDateTimeContextLabel').textContent = `${MONTH_NAMES_THAI[currentMonthScope]} ${currentYearScope}`;
  document.getElementById('modalCalendarDayHeading').textContent = `วัน${weekDayNamesThaiArray[targetDayOfWeekNameIndex]}ที่ ${dayIndex}`;
  document.getElementById('modalDayNoteTextarea').value = targetDayDataInstance.note || '';
  document.getElementById('modalPedometerStepInput').value = targetDayDataInstance.steps || '';
  document.getElementById('modalCalorieInInput').value = targetDayDataInstance.calorieIn || '';
  document.getElementById('modalCalorieOutInput').value = targetDayDataInstance.calorieOut || '';

  const paletteContainer = document.getElementById('modalColorPaletteContainer');
  paletteContainer.innerHTML = COLORS.map(c => `
    <div class="color-dot ${targetDayDataInstance.color === c.value ? 'active ring-4 ring-purple-600/30' : ''}"
         style="background:${c.value || 'linear-gradient(135deg, #ffffff 0%, #f1f5f9 100%)'}"
         onclick="executeDayColorAssignment('${c.value}')" title="${c.name}"></div>
  `).join('');

  const moodContainer = document.getElementById('modalMoodTrackerContainer');
  const availableMoodVectors = ['🌟 ยอดเยี่ยม', '😊 มีความสุข', '😐 ทรงตัว', '😥 เครียด', '🔋 เหนื่อยล้า'];
  moodContainer.innerHTML = availableMoodVectors.map(m => `
    <button onclick="executeMoodAssignment('${m}')" class="px-2 py-1 rounded-xl border text-[11px] font-bold transition-all ${targetDayDataInstance.mood === m ? 'bg-purple-600 text-white shadow-md' : 'bg-slate-50 text-gray-700 hover:bg-gray-100'}">${m.split(' ')[0]}</button>
  `).join('');

  const stickerPaletteContainer = document.getElementById('modalStickerPaletteContainer');
  stickerPaletteContainer.innerHTML = STICKERS.map(st => `
    <span class="sticker p-1 rounded-lg text-lg ${targetDayDataInstance.stickers && targetDayDataInstance.stickers.includes(st) ? 'bg-purple-600/20 active scale-110 border border-purple-400' : ''}"
          onclick="toggleStickerVectorState('${st}')">${st}</span>
  `).join('');

  syncWaterIntakeUiRepresentation(targetDayDataInstance.water || 0);
  compileModalTaskUiRows();
  compileModalFinancialLedgerUiRows();

  const modalOverlayElement = document.getElementById('dynamicExecutionModal');
  modalOverlayElement.classList.remove('hidden');
  modalOverlayElement.classList.add('flex');
  setTimeout(() => {
    modalOverlayElement.classList.remove('opacity-0');
    modalOverlayElement.querySelector('div').classList.remove('scale-95', 'opacity-0');
    modalOverlayElement.querySelector('div').classList.add('scale-100', 'opacity-100');
  }, 10);
  triggerAudioSynthesisCue('click');
}

function closeDynamicExecutionModal() {
  if (activeTargetDayIndex) {
    const targetDayDataInstance = getDayRecordInstance(currentYearScope, currentMonthScope, activeTargetDayIndex);
    targetDayDataInstance.note = document.getElementById('modalDayNoteTextarea').value;
    targetDayDataInstance.steps = parseInt(document.getElementById('modalPedometerStepInput').value) || 0;
    targetDayDataInstance.calorieIn = parseInt(document.getElementById('modalCalorieInInput').value) || 0;
    targetDayDataInstance.calorieOut = parseInt(document.getElementById('modalCalorieOutInput').value) || 0;
    
    saveDatabaseToLocalStorage();
    renderCalendarMainWorkspace();
  }
  
  const modalOverlayElement = document.getElementById('dynamicExecutionModal');
  modalOverlayElement.classList.add('opacity-0');
  modalOverlayElement.querySelector('div').classList.remove('scale-100', 'opacity-100');
  modalOverlayElement.querySelector('div').classList.add('scale-95', 'opacity-0');
  
  setTimeout(() => {
    modalOverlayElement.classList.add('hidden');
    modalOverlayElement.classList.remove('flex');
    activeTargetDayIndex = null;
  }, 200);
  triggerAudioSynthesisCue('click');
}

function executeDayColorAssignment(selectedColorHexValue) {
  if (!activeTargetDayIndex) return;
  getDayRecordInstance(currentYearScope, currentMonthScope, activeTargetDayIndex).color = selectedColorHexValue;
  saveDatabaseToLocalStorage();
  openDynamicExecutionModal(activeTargetDayIndex);
  triggerGlobalToastNotification('🎨 อัปเดตโครงสร้างสีประจำวันสำเร็จ');
}

function executeMoodAssignment(selectedMoodString) {
  if (!activeTargetDayIndex) return;
  const currentInstance = getDayRecordInstance(currentYearScope, currentMonthScope, activeTargetDayIndex);
  currentInstance.mood = (currentInstance.mood === selectedMoodString) ? '' : selectedMoodString;
  saveDatabaseToLocalStorage();
  openDynamicExecutionModal(activeTargetDayIndex);
  triggerAudioSynthesisCue('success');
}

function toggleStickerVectorState(targetStickerCharacter) {
  if (!activeTargetDayIndex) return;
  const currentInstance = getDayRecordInstance(currentYearScope, currentMonthScope, activeTargetDayIndex);
  if (!currentInstance.stickers) currentInstance.stickers = [];
  const existingStickerIndex = currentInstance.stickers.indexOf(targetStickerCharacter);
  
  if (existingStickerIndex > -1) {
    currentInstance.stickers.splice(existingStickerIndex, 1);
    triggerAudioSynthesisCue('delete');
  } else {
    currentInstance.stickers.push(targetStickerCharacter);
    triggerAudioSynthesisCue('success');
    triggerConfettiParticleBurst(targetStickerCharacter);
  }
  saveDatabaseToLocalStorage();
  openDynamicExecutionModal(activeTargetDayIndex);
}

function syncWaterIntakeUiRepresentation(waterAmountValue) {
  const container = document.getElementById('modalWaterIntakeGlassContainer');
  let waterHtmlAccumulator = '';
  for (let glassIndex = 1; glassIndex <= 8; glassIndex++) {
    const isFilledFlag = glassIndex <= waterAmountValue;
    waterHtmlAccumulator += `
      <span onclick="executeWaterIntakeAdjustment(${glassIndex})" class="cursor-pointer text-xl transition-transform hover:scale-125 inline-block filter ${isFilledFlag ? 'drop-shadow-md brightness-110' : 'opacity-20 grayscale'}">💧</span>
    `;
  }
  container.innerHTML = waterHtmlAccumulator;
  document.getElementById('modalWaterTextCounter').textContent = `${waterAmountValue} / 8 แก้ว`;
}

function executeWaterIntakeAdjustment(selectedGlassCountTarget) {
  if (!activeTargetDayIndex) return;
  const currentInstance = getDayRecordInstance(currentYearScope, currentMonthScope, activeTargetDayIndex);
  currentInstance.water = (currentInstance.water === selectedGlassCountTarget) ? selectedGlassCountTarget - 1 : selectedGlassCountTarget;
  if (currentInstance.water < 0) currentInstance.water = 0;
  saveDatabaseToLocalStorage();
  syncWaterIntakeUiRepresentation(currentInstance.water);
  triggerAudioSynthesisCue('success');
}

function insertNewTaskRow() {
  if (!activeTargetDayIndex) return;
  const currentInstance = getDayRecordInstance(currentYearScope, currentMonthScope, activeTargetDayIndex);
  currentInstance.tasks.push({ text: '', done: false, priority: 'medium', category: 'general', dueTime: '' });
  saveDatabaseToLocalStorage();
  compileModalTaskUiRows();
  triggerAudioSynthesisCue('click');
}

function compileModalTaskUiRows() {
  if (!activeTargetDayIndex) return;
  const currentInstance = getDayRecordInstance(currentYearScope, currentMonthScope, activeTargetDayIndex);
  const container = document.getElementById('modalTaskListRowsContainer');
  
  if (currentInstance.tasks.length === 0) {
    container.innerHTML = `<p class="text-center text-gray-400 text-xs py-4 font-medium border border-dashed rounded-xl bg-slate-50">ไม่มีภารกิจผูกมัดในระบบสำหรับวันนี้</p>`;
    return;
  }

  container.innerHTML = currentInstance.tasks.map((taskItem, itemIndex) => `
    <div class="flex flex-col md:flex-row items-stretch md:items-center gap-2 bg-slate-50 p-2 rounded-xl border border-gray-200 shadow-sm relative transition-all hover:bg-white">
      <input type="checkbox" ${taskItem.done ? 'checked' : ''} onchange="toggleTaskCompletionVector(${itemIndex})" class="w-4 h-4 text-purple-600 focus:ring-purple-400 rounded cursor-pointer border-gray-300 mt-1 md:mt-0">
      
      <input type="text" value="${escapeHtmlSpecialChars(taskItem.text)}" oninput="updateTaskTextProperty(${itemIndex}, this.value)" placeholder="รายละเอียดงานที่ต้องพิชิต..." class="flex-1 bg-transparent text-xs font-bold focus:outline-none text-gray-700 ${taskItem.done ? 'line-through text-gray-400' : ''}">
      
      <div class="flex items-center gap-1 flex-wrap">
        <select onchange="updateTaskPriorityProperty(${itemIndex}, this.value)" class="px-1 py-0.5 text-[10px] font-black border rounded-md bg-white text-gray-600 focus:outline-none">
          <option value="low" ${taskItem.priority === 'low' ? 'selected' : ''}>ความสำคัญต่ำ</option>
          <option value="medium" ${taskItem.priority === 'medium' ? 'selected' : ''}>ปานกลาง</option>
          <option value="high" ${taskItem.priority === 'high' ? 'selected' : ''}>🔴 เร่งด่วนสูง</option>
        </select>

        <select onchange="updateTaskCategoryProperty(${itemIndex}, this.value)" class="px-1 py-0.5 text-[10px] font-black border rounded-md bg-white text-purple-600 focus:outline-none">
          <option value="general" ${taskItem.category === 'general' ? 'selected' : ''}>หมวดทั่วไป</option>
          <option value="work" ${taskItem.category === 'work' ? 'selected' : ''}>💼 การงาน</option>
          <option value="finance" ${taskItem.category === 'finance' ? 'selected' : ''}>💰 การเงิน</option>
          <option value="health" ${taskItem.category === 'health' ? 'selected' : ''}>💪 สุขภาพ</option>
          <option value="personal" ${taskItem.category === 'personal' ? 'selected' : ''}>🏠 ส่วนตัว</option>
        </select>

        <input type="time" value="${taskItem.dueTime || ''}" onchange="updateTaskDueTimeProperty(${itemIndex}, this.value)" class="px-1 py-0.5 text-[10px] border rounded-md font-mono focus:outline-none">

        <button onclick="deleteTaskRowInstance(${itemIndex})" class="p-1 text-red-400 hover:text-red-600 text-xs transition-colors rounded hover:bg-red-50">🗑</button>
      </div>
    </div>
  `).join('');
}

function toggleTaskCompletionVector(itemIndex) {
  if (!activeTargetDayIndex) return;
  const currentInstance = getDayRecordInstance(currentYearScope, currentMonthScope, activeTargetDayIndex);
  currentInstance.tasks[itemIndex].done = !currentInstance.tasks[itemIndex].done;
  saveDatabaseToLocalStorage();
  compileModalTaskUiRows();
  triggerAudioSynthesisCue('success');
}

function updateTaskTextProperty(itemIndex, directValue) {
  if (!activeTargetDayIndex) return;
  getDayRecordInstance(currentYearScope, currentMonthScope, activeTargetDayIndex).tasks[itemIndex].text = directValue;
  saveDatabaseToLocalStorage();
}

function updateTaskPriorityProperty(itemIndex, directValue) {
  if (!activeTargetDayIndex) return;
  getDayRecordInstance(currentYearScope, currentMonthScope, activeTargetDayIndex).tasks[itemIndex].priority = directValue;
  saveDatabaseToLocalStorage();
}

function updateTaskCategoryProperty(itemIndex, directValue) {
  if (!activeTargetDayIndex) return;
  getDayRecordInstance(currentYearScope, currentMonthScope, activeTargetDayIndex).tasks[itemIndex].category = directValue;
  saveDatabaseToLocalStorage();
}

function updateTaskDueTimeProperty(itemIndex, directValue) {
  if (!activeTargetDayIndex) return;
  getDayRecordInstance(currentYearScope, currentMonthScope, activeTargetDayIndex).tasks[itemIndex].dueTime = directValue;
  saveDatabaseToLocalStorage();
}

function deleteTaskRowInstance(itemIndex) {
  if (!activeTargetDayIndex) return;
  getDayRecordInstance(currentYearScope, currentMonthScope, activeTargetDayIndex).tasks.splice(itemIndex, 1);
  saveDatabaseToLocalStorage();
  compileModalTaskUiRows();
  triggerAudioSynthesisCue('delete');
}

function insertNewFinancialRow() {
  if (!activeTargetDayIndex) return;
  const currentInstance = getDayRecordInstance(currentYearScope, currentMonthScope, activeTargetDayIndex);
  if (!currentInstance.finance) currentInstance.finance = [];
  currentInstance.finance.push({ type: 'expense', amount: 0, description: '' });
  saveDatabaseToLocalStorage();
  compileModalFinancialLedgerUiRows();
  triggerAudioSynthesisCue('click');
}

function compileModalFinancialLedgerUiRows() {
  if (!activeTargetDayIndex) return;
  const currentInstance = getDayRecordInstance(currentYearScope, currentMonthScope, activeTargetDayIndex);
  const container = document.getElementById('modalFinancialLedgerRowsContainer');
  
  if (!currentInstance.finance || currentInstance.finance.length === 0) {
    container.innerHTML = `<p class="text-center text-gray-400 text-xs py-3 font-medium border border-dashed rounded-xl bg-slate-50">ไม่มีข้อมูลธุรกรรมทางการเงินในวันนี้</p>`;
    return;
  }

  container.innerHTML = currentInstance.finance.map((txItem, txIndex) => `
    <div class="flex items-center gap-1.5 bg-slate-50 p-1.5 rounded-lg border border-gray-200">
      <select onchange="updateFinancialTypeProperty(${txIndex}, this.value)" class="px-1 py-0.5 text-[10px] font-black border rounded bg-white text-gray-700 focus:outline-none">
        <option value="expense" ${txItem.type === 'expense' ? 'selected' : ''}>📉 รายจ่าย</option>
        <option value="income" ${txItem.type === 'income' ? 'selected' : ''}>📈 รายรับ</option>
      </select>
      <input type="number" value="${txItem.amount || 0}" oninput="updateFinancialAmountProperty(${txIndex}, this.value)" placeholder="จำนวนเงิน" class="w-20 px-1 py-0.5 border text-xs font-bold rounded focus:outline-none text-center">
      <input type="text" value="${escapeHtmlSpecialChars(txItem.description || '')}" oninput="updateFinancialDescriptionProperty(${txIndex}, this.value)" placeholder="รายละเอียด/หมายเหตุการโอน..." class="flex-1 bg-transparent text-xs font-medium focus:outline-none px-1 text-gray-600">
      <button onclick="deleteFinancialRowInstance(${txIndex})" class="text-red-400 hover:text-red-500 text-xs px-1">✕</button>
    </div>
  `).join('');
}

function updateFinancialTypeProperty(txIndex, directValue) {
  if (!activeTargetDayIndex) return;
  getDayRecordInstance(currentYearScope, currentMonthScope, activeTargetDayIndex).finance[txIndex].type = directValue;
  saveDatabaseToLocalStorage();
  triggerAudioSynthesisCue('click');
}

function updateFinancialAmountProperty(txIndex, directValue) {
  if (!activeTargetDayIndex) return;
  getDayRecordInstance(currentYearScope, currentMonthScope, activeTargetDayIndex).finance[txIndex].amount = parseFloat(directValue) || 0;
  saveDatabaseToLocalStorage();
}

function updateFinancialDescriptionProperty(txIndex, directValue) {
  if (!activeTargetDayIndex) return;
  getDayRecordInstance(currentYearScope, currentMonthScope, activeTargetDayIndex).finance[txIndex].description = directValue;
  saveDatabaseToLocalStorage();
}

function deleteFinancialRowInstance(txIndex) {
  if (!activeTargetDayIndex) return;
  getDayRecordInstance(currentYearScope, currentMonthScope, activeTargetDayIndex).finance.splice(txIndex, 1);
  saveDatabaseToLocalStorage();
  compileModalFinancialLedgerUiRows();
  triggerAudioSynthesisCue('delete');
}

function executeDayDataPurge() {
  if (!activeTargetDayIndex) return;
  if (!confirm('แจ้งเตือนระบบ: คุณแน่ใจหรือไม่ที่จะทำการล้างข้อมูลย่อยทั้งหมดของวันนี้ทิ้งอย่างกู้คืนไม่ได้?')) return;
  const currentKey = computeTargetMonthCompositeKey();
  globalDatabaseState[currentKey][activeTargetDayIndex] = {
    color: '', note: '', tasks: [], stickers: [], mood: '', water: 0, steps: 0, calorieIn: 0, calorieOut: 0, finance: []
  };
  saveDatabaseToLocalStorage();
  closeDynamicExecutionModal();
  triggerGlobalToastNotification('🧹 เคลียร์สภาวะข้อมูลในวันนี้เสร็จสิ้น');
}

function purgeEntireDatabase() {
  if (!confirm('⚠️ การล้างข้อมูลระดับโครงสร้างขั้นสูง: ยืนยันการสั่งทำลายฐานข้อมูลปฏิทินตลอดทั้งปีใช่หรือไม่? ข้อมูลทั้งหมดจะหายไปในทันที!')) return;
  globalDatabaseState = {};
  saveDatabaseToLocalStorage();
  renderCalendarMainWorkspace();
  triggerGlobalToastNotification('🔥 ทำลายฐานข้อมูลทั้งหมดในระบบคลาวด์บราวเซอร์เสร็จสมบูรณ์');
}

function triggerDataExport() {
  const fileDataPayload = {
    architectureVersion: '2.5-hyper-faceted-engine',
    database: globalDatabaseState,
    settings: globalSettingsState,
    timestamp: new Date().toISOString()
  };
  const stringifiedBlob = new Blob([JSON.stringify(fileDataPayload, null, 2)], { type: 'application/json' });
  const objectUrlUrl = URL.createObjectURL(stringifiedBlob);
  const downloadTriggerAnchor = document.createElement('a');
  downloadTriggerAnchor.href = objectUrlUrl;
  downloadTriggerAnchor.download = `MYC${currentYearScope}.json`;
  downloadTriggerAnchor.click();
  URL.revokeObjectURL(objectUrlUrl);
  triggerGlobalToastNotification('💾 สำรองและส่งออกฐานข้อมูลความปลอดภัยสูงสำเร็จแล้ว');
}

function executeAnonymizedExport() {
  const dynamicClonedDatabase = JSON.parse(JSON.stringify(globalDatabaseState));
  Object.keys(dynamicClonedDatabase).forEach(monthKey => {
    Object.keys(dynamicClonedDatabase[monthKey]).forEach(dayKey => {
      const dayData = dynamicClonedDatabase[monthKey][dayKey];
      dayData.note = dayData.note ? "ANONYMIZED_NOTE_TEXT" : "";
      if (dayData.tasks) {
        dayData.tasks.forEach(t => { t.text = "ANONYMIZED_TASK_TEXT"; });
      }
      if (dayData.finance) {
        dayData.finance.forEach(f => { f.description = "ANONYMIZED_FINANCE_DESCRIPTION"; });
      }
    });
  });
  
  const anonymizedPayload = {
    architectureVersion: '2.5-anonymized',
    database: dynamicClonedDatabase,
    settings: globalSettingsState,
    timestamp: new Date().toISOString()
  };
  const stringifiedBlob = new Blob([JSON.stringify(anonymizedPayload, null, 2)], { type: 'application/json' });
  const objectUrlUrl = URL.createObjectURL(stringifiedBlob);
  const downloadTriggerAnchor = document.createElement('a');
  downloadTriggerAnchor.href = objectUrlUrl;
  downloadTriggerAnchor.download = `ANONYMIZED_CALENDAR_DATA.json`;
  downloadTriggerAnchor.click();
  URL.revokeObjectURL(objectUrlUrl);
  triggerGlobalToastNotification('🛡️ ส่งออกข้อมูลแบบเข้ารหัสปิดบังตัวตนสำเร็จ');
}

function triggerDataImport(eventInstance) {
  const targetedFile = eventInstance.target.files[0];
  if (!targetedFile) return;
  const fileReaderInstance = new FileReader();
  fileReaderInstance.onload = (fileLoadedEvent) => {
    try {
      const parsedImportedJsonPayload = JSON.parse(fileLoadedEvent.target.result);
      if (parsedImportedJsonPayload.database) {
        globalDatabaseState = parsedImportedJsonPayload.database;
      } else {
        globalDatabaseState = parsedImportedJsonPayload;
      }
      if (parsedImportedJsonPayload.settings) {
        globalSettingsState = parsedImportedJsonPayload.settings;
        applyDynamicSettingsConfiguration();
      }
      saveDatabaseToLocalStorage();
      renderCalendarMainWorkspace();
      triggerGlobalToastNotification('📥 บูรณาการโครงสร้างข้อมูลใหม่สำเร็จเสร็จสิ้น');
    } catch (exceptionErr) {
      triggerGlobalToastNotification('❌ ข้อผิดพลาด: โครงสร้างไฟล์ JSON ผิดพลาด ชำรุดหรือไม่เข้าพวก');
    }
  };
  fileReaderInstance.readAsText(targetedFile);
  eventInstance.target.value = '';
}

function executeThemeChange(selectedThemeClassName) {
  globalSettingsState.theme = selectedThemeClassName;
  saveSettingsToLocalStorage();
  applyDynamicThemeStyles();
}

function applyDynamicThemeStyles() {
  const bodyElement = document.getElementById('applicationBody');
  const availableThemeClassesArray = ['theme-sakura', 'theme-ocean', 'theme-forest', 'theme-sunset', 'theme-dark'];
  bodyElement.classList.remove(...availableThemeClassesArray);
  
  const activeTheme = globalSettingsState.theme || 'theme-default';
  if (activeTheme !== 'theme-default') {
    bodyElement.classList.add(activeTheme);
  }
}

function triggerCardSizeAdjustment(selectedCardSizeString) {
  globalSettingsState.cardSize = selectedCardSizeString;
  saveSettingsToLocalStorage();
  renderCalendarMainWorkspace();
}

function triggerFontFamilySwitch(selectedFontClassName) {
  globalSettingsState.fontFamily = selectedFontClassName;
  saveSettingsToLocalStorage();
  applyDynamicTypographyStyles();
}

function applyDynamicTypographyStyles() {
  const bodyElement = document.getElementById('applicationBody');
  const fullySupportedFontsArray = ['font-sans', 'font-display', 'font-sarabun', 'font-montserrat'];
  bodyElement.classList.remove(...fullySupportedFontsArray);
  bodyElement.classList.add(globalSettingsState.fontFamily || 'font-sans');
}

function toggleAudioMuteState() {
  globalSettingsState.audioMuted = !globalSettingsState.audioMuted;
  saveSettingsToLocalStorage();
  document.getElementById('audioMuteToggleButton').textContent = globalSettingsState.audioMuted ? 'ปิด' : 'เปิด';
}

function triggerAudioSynthesisCue(cueTypeString) {
  if (globalSettingsState.audioMuted) return;
  try {
    const AudioContextClass = window.AudioContext || window.webkitAudioContext;
    if (!AudioContextClass) return;
    const ctx = new AudioContextClass();
    const oscNode = ctx.createOscillator();
    const gainNode = ctx.createGain();
    
    oscNode.connect(gainNode);
    gainNode.connect(ctx.destination);
    
    if (cueTypeString === 'click') {
      oscNode.frequency.setValueAtTime(587.33, ctx.currentTime);
      gainNode.gain.setValueAtTime(0.08, ctx.currentTime);
      gainNode.gain.exponentialRampToValueAtTime(0.00001, ctx.currentTime + 0.08);
      oscNode.start();
      oscNode.stop(ctx.currentTime + 0.08);
    } else if (cueTypeString === 'success') {
      oscNode.frequency.setValueAtTime(880, ctx.currentTime);
      gainNode.gain.setValueAtTime(0.1, ctx.currentTime);
      gainNode.gain.exponentialRampToValueAtTime(0.00001, ctx.currentTime + 0.2);
      oscNode.start();
      oscNode.stop(ctx.currentTime + 0.2);
    } else if (cueTypeString === 'delete') {
      oscNode.frequency.setValueAtTime(220, ctx.currentTime);
      gainNode.gain.setValueAtTime(0.12, ctx.currentTime);
      gainNode.gain.exponentialRampToValueAtTime(0.00001, ctx.currentTime + 0.25);
      oscNode.start();
      oscNode.stop(ctx.currentTime + 0.25);
    }
    
    if (navigator.vibrate) navigator.vibrate(12);
  } catch (e) {}
}

function triggerGlobalToastNotification(messageContentString) {
  const toastContainerElement = document.getElementById('globalToastNotificationPopup');
  toastContainerElement.innerHTML = `<span>ℹ️</span> <span>${messageContentString}</span>`;
  toastContainerElement.classList.remove('hidden', 'opacity-0', 'translate-y-20', 'pointer-events-none');
  toastContainerElement.classList.add('opacity-100', 'translate-y-0');
  
  if (window.toastActiveTimerTimeoutReference) clearTimeout(window.toastActiveTimerTimeoutReference);
  window.toastActiveTimerTimeoutReference = setTimeout(() => {
    toastContainerElement.classList.add('opacity-0', 'translate-y-20', 'pointer-events-none');
    toastContainerElement.classList.remove('opacity-100', 'translate-y-0');
  }, 3000);
}

function executePomodoroCommand(commandString) {
  if (commandString === 'start') {
    if (pomodoroTimerCountdownReference) return;
    pomodoroTimerCountdownReference = setInterval(() => {
      pomodoroRemainingSeconds--;
      refreshPomodoroTimerUiDisplay();
      if (pomodoroRemainingSeconds <= 0) {
        clearInterval(pomodoroTimerCountdownReference);
        pomodoroTimerCountdownReference = null;
        triggerAudioSynthesisCue('success');
        triggerGlobalToastNotification('⏰ สิ้นสุดช่วงเวลาโปรโมโดโร แนะนำให้ยืดเหยียดร่างกายเพื่อสุขภาพ');
      }
    }, 1000);
    triggerGlobalToastNotification('⏱️ เริ่มระบบจับเวลาโฟกัสเข้มข้นแล้ว');
  } else if (commandString === 'pause') {
    clearInterval(pomodoroTimerCountdownReference);
    pomodoroTimerCountdownReference = null;
    triggerGlobalToastNotification('⏸️ หยุดเวลาชั่วคราว');
  } else if (commandString === 'reset') {
    clearInterval(pomodoroTimerCountdownReference);
    pomodoroTimerCountdownReference = null;
    pomodoroRemainingSeconds = 1500;
    refreshPomodoroTimerUiDisplay();
    triggerGlobalToastNotification('🧹 ล้างเวลาโปรโมโดโรกลับสู่ค่ามาตรฐานสำเร็จ');
  }
  triggerAudioSynthesisCue('click');
}

function setPomodoroInterval(minutesValue) {
  clearInterval(pomodoroTimerCountdownReference);
  pomodoroTimerCountdownReference = null;
  pomodoroRemainingSeconds = minutesValue * 60;
  refreshPomodoroTimerUiDisplay();
  triggerGlobalToastNotification(`⏱️ สลับโหมดช่วงเวลาเป็นจำนวน ${minutesValue} นาที`);
}

function refreshPomodoroTimerUiDisplay() {
  const minutesSegment = Math.floor(pomodoroRemainingSeconds / 60);
  const secondsSegment = pomodoroRemainingSeconds % 60;
  document.getElementById('pomodoroTimeDisplay').textContent = `${minutesSegment.toString().padStart(2, '0')}:${secondsSegment.toString().padStart(2, '0')}`;
}

function saveMonthlyTargetObjective(textValue) {
  const key = computeTargetMonthCompositeKey();
  if (!globalSettingsState.monthlyTargets) globalSettingsState.monthlyTargets = {};
  globalSettingsState.monthlyTargets[key] = textValue;
  saveSettingsToLocalStorage();
}

function loadMonthlyTargetObjective() {
  const key = computeTargetMonthCompositeKey();
  const value = (globalSettingsState.monthlyTargets && globalSettingsState.monthlyTargets[key]) ? globalSettingsState.monthlyTargets[key] : '';
  document.getElementById('monthlyGlobalTargetObjectiveInput').value = value;
}

function applyDynamicSettingsConfiguration() {
  document.getElementById('themeSelector').value = globalSettingsState.theme || 'theme-default';
  document.getElementById('uiCardSizeSelector').value = globalSettingsState.cardSize || 'medium';
  document.getElementById('globalFontSelector').value = globalSettingsState.fontFamily || 'font-sans';
  document.getElementById('audioMuteToggleButton').textContent = globalSettingsState.audioMuted ? 'ปิด' : 'เปิด';
  
  applyDynamicThemeStyles();
  applyDynamicTypographyStyles();
}

function applyDynamicStylingOverrides() {
  loadMonthlyTargetObjective();
}

function escapeHtmlSpecialChars(rawTextString) {
  if (!rawTextString) return '';
  const divElementWrapper = document.createElement('div');
  divElementWrapper.textContent = rawTextString;
  return divElementWrapper.innerHTML;
}

function compileCustomBackgroundGradient() {
  const colOne = document.getElementById('gradientColorPickerOne').value;
  const colTwo = document.getElementById('gradientColorPickerTwo').value;
  const angle = document.getElementById('gradientAngleSelector').value;
  document.getElementById('applicationBody').style.background = `linear-gradient(${angle}, ${colOne}, ${colTwo})`;
  globalSettingsState.customGradient = { c1: colOne, c2: colTwo, angle: angle };
  saveSettingsToLocalStorage();
}

function loadCustomBackgroundGradientIfPresent() {
  if (globalSettingsState.customGradient) {
    const cg = globalSettingsState.customGradient;
    document.getElementById('gradientColorPickerOne').value = cg.c1;
    document.getElementById('gradientColorPickerTwo').value = cg.c2;
    document.getElementById('gradientAngleSelector').value = cg.angle;
    document.getElementById('applicationBody').style.background = `linear-gradient(${cg.angle}, ${cg.c1}, ${cg.c2})`;
  }
}

function triggerConfettiParticleBurst(stickerEmojiChar) {
  const canvas = document.getElementById('particleCanvas');
  const ctx = canvas.getContext('2d');
  canvas.width = window.innerWidth;
  canvas.height = window.innerHeight;
  
  const particlesArray = [];
  const paletteColorsArray = ['#a855f7', '#ec4899', '#3b82f6', '#22c55e', '#eab308'];

  for (let particleIndex = 0; particleIndex < 35; particleIndex++) {
    particlesArray.push({
      x: window.innerWidth / 2,
      y: window.innerHeight / 2,
      radius: Math.random() * 4 + 2,
      color: paletteColorsArray[Math.floor(Math.random() * paletteColorsArray.length)],
      speedX: (Math.random() - 0.5) * 12,
      speedY: (Math.random() - 0.5) * 12 - 4,
      alpha: 1
    });
  }

  function executeParticleAnimationLoop() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    let activeParticlesRemainingFlag = false;

    particlesArray.forEach(p => {
      p.x += p.speedX;
      p.y += p.speedY;
      p.speedY += 0.18;
      p.alpha -= 0.015;

      if (p.alpha > 0) {
        activeParticlesRemainingFlag = true;
        ctx.save();
        ctx.globalAlpha = p.alpha;
        ctx.beginPath();
        ctx.arc(p.x, p.y, p.radius, 0, Math.PI * 2);
        ctx.fillStyle = p.color;
        ctx.fill();
        ctx.restore();
      }
    });

    if (activeParticlesRemainingFlag) {
      requestAnimationFrame(executeParticleAnimationLoop);
    } else {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
    }
  }
  executeParticleAnimationLoop();
}

function registerSystemEventHooks() {
  document.getElementById('dynamicExecutionModal').addEventListener('click', (e) => {
    if (e.target.id === 'dynamicExecutionModal') closeDynamicExecutionModal();
  });

  document.addEventListener('keydown', (e) => {
    const modalEl = document.getElementById('dynamicExecutionModal');
    if (e.key === 'Escape' && !modalEl.classList.contains('hidden')) {
      closeDynamicExecutionModal();
    }
    if (modalEl.classList.contains('hidden')) {
      if (e.key === 'ArrowLeft') shiftTemporalMonth(-1);
      if (e.key === 'ArrowRight') shiftTemporalMonth(1);
    }
  });
  
  window.addEventListener('resize', () => {
    const canvas = document.getElementById('particleCanvas');
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
  });
}

const COLORS = [
  { name: 'ไม่ระบุ', value: '' },
  { name: 'แดง', value: '#fee2e2' },
  { name: 'ส้ม', value: '#ffedd5' },
  { name: 'เหลือง', value: '#fef9c3' },
  { name: 'เขียว', value: '#dcfce7' },
  { name: 'ฟ้า', value: '#dbeafe' },
  { name: 'คราม', value: '#e0e7ff' },
  { name: 'ม่วง', value: '#f3e8ff' },
  { name: 'ชมพู', value: '#fce7f3' },
  { name: 'ดำ', value: '#1f2937' }
];

function triggerApplicationInitializationLifecycle() {
  document.getElementById('systemQuoteDisplay').textContent = "📜 " + SYSTEM_QUOTES[Math.floor(Math.random() * SYSTEM_QUOTES.length)];
  applyDynamicSettingsConfiguration();
  loadCustomBackgroundGradientIfPresent();
  registerSystemEventHooks();
  renderCalendarMainWorkspace();
}

triggerApplicationInitializationLifecycle();
</script>

</body>
</html>
