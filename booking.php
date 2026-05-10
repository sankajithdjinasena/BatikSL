<?php
// booking.php - Experience Booking Page
require_once 'config/database.php';
?>
<!DOCTYPE html>
<html>
<head><title>Book a Live Batik Session | BatikSL</title><link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css"><script src="https://cdn.jsdelivr.net/npm/flatpickr"></script></head>
<body>
<?php include 'includes/nav.php'; ?>
<div class="hero-bg-small bg-teal-700 text-white py-16"><div class="container mx-auto px-4 text-center"><h1 class="text-4xl font-bold">Book a Live Batik Session</h1><p class="text-xl mt-2">2 hours of hands-on learning with master artisans in Kandy</p></div></div>

<div class="container mx-auto px-4 py-12 max-w-6xl">
    <div class="grid md:grid-cols-3 gap-8 mb-12">
        <div class="bg-white p-6 rounded-xl shadow text-center"><i class="fas fa-clock text-3xl teal-text mb-3"></i><h3 class="font-bold">Duration</h3><p>2 Hours</p></div>
        <div class="bg-white p-6 rounded-xl shadow text-center"><i class="fas fa-users text-3xl teal-text mb-3"></i><h3 class="font-bold">Group Size</h3><p>Max 8 people</p></div>
        <div class="bg-white p-6 rounded-xl shadow text-center"><i class="fas fa-map-marker-alt text-3xl teal-text mb-3"></i><h3 class="font-bold">Location</h3><p>Kandy Workshop</p></div>
    </div>

    <div class="flex flex-col lg:flex-row gap-8">
        <div class="lg:w-2/3 bg-white rounded-xl shadow-md p-6"><h2 class="text-2xl font-bold mb-4">Select Date & Time</h2><input type="text" id="datepicker" placeholder="Select Date" class="w-full border rounded p-2 mb-4"><div class="flex gap-2 mb-6"><button class="time-slot bg-teal-100 teal-text px-4 py-2 rounded-full">10:00 AM</button><button class="time-slot bg-gray-100 px-4 py-2 rounded-full">2:00 PM</button><button class="time-slot bg-teal-100 teal-text px-4 py-2 rounded-full">4:00 PM</button></div><div class="mb-6"><label class="block font-semibold mb-2">Number of People</label><div class="flex items-center"><button onclick="changeGroupSize(-1)" class="border px-3 py-1 rounded-l">-</button><span id="groupSize" class="px-6 py-1 border-t border-b">2</span><button onclick="changeGroupSize(1)" class="border px-3 py-1 rounded-r">+</button></div></div><div class="mb-4"><input type="text" placeholder="Your Name" class="w-full border rounded p-2 mb-2"><input type="email" placeholder="Email" class="w-full border rounded p-2 mb-2"><input type="tel" placeholder="Phone" class="w-full border rounded p-2 mb-2"><textarea placeholder="Special requests (dietary, accessibility...)" class="w-full border rounded p-2"></textarea></div><button class="w-full bg-teal-600 text-white py-3 rounded-lg font-semibold">Confirm Booking (Pay 30% Deposit)</button></div>
        <div class="lg:w-1/3"><div class="bg-gray-50 rounded-xl p-6 sticky top-24"><h3 class="font-bold text-xl mb-4">Booking Summary</h3><div class="space-y-2"><div class="flex justify-between"><span>Date:</span><span id="summaryDate">Not selected</span></div><div class="flex justify-between"><span>Time:</span><span id="summaryTime">-</span></div><div class="flex justify-between"><span>People:</span><span id="summaryPeople">2</span></div><div class="border-t pt-2 mt-2"><div class="flex justify-between font-bold"><span>Deposit (30%)</span><span id="depositAmount">LKR 2,700</span></div><p class="text-xs text-gray-500 mt-2">Full session: LKR 9,000 (balance paid on arrival)</p></div></div></div></div>
    </div>
</div>

<script>
flatpickr("#datepicker", { minDate: "today", dateFormat: "Y-m-d", onChange: function(selectedDates) { document.getElementById('summaryDate').innerText = selectedDates[0].toDateString(); } });
let groupSize = 2;
function changeGroupSize(delta){ let newVal = groupSize + delta; if(newVal>=1 && newVal<=8){ groupSize = newVal; document.getElementById('groupSize').innerText = groupSize; document.getElementById('summaryPeople').innerText = groupSize; let deposit = groupSize * 9000 * 0.3; document.getElementById('depositAmount').innerText = 'LKR ' + deposit.toLocaleString(); } }
document.querySelectorAll('.time-slot').forEach(btn=>{ btn.addEventListener('click',()=>{ document.querySelectorAll('.time-slot').forEach(b=>b.classList.remove('bg-teal-600','text-white','bg-teal-100')); btn.classList.add('bg-teal-600','text-white'); document.getElementById('summaryTime').innerText = btn.innerText; })});
</script>
<?php include 'includes/footer.php'; ?>
</body>
</html>