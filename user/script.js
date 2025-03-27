const validTimeSlots = [
    "08:10", "09:00", "09:50",
    "10:05", "10:55", "11:45",
    "13:00", "13:25", "13:50",
    "14:40", "15:30",
    "15:45", "16:35", "17:25"
];

function timeToMinutes(time) {
    const [hours, minutes] = time.split(':').map(Number);
    return hours * 60 + minutes;
}

function minutesToTime(minutes) {
    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;
    return `${hours.toString().padStart(2, '0')}:${mins.toString().padStart(2, '0')}`;
}

function findNextValidSlot(minutes) {
    let nextSlot = validTimeSlots[0];
    for (const slot of validTimeSlots) {
        const slotMinutes = timeToMinutes(slot);
        if (slotMinutes >= minutes) {
            nextSlot = slot;
            break;
        }
    }
    return nextSlot;
}

const startTimeSelect = document.getElementById('startTime');
validTimeSlots.forEach(time => {
    const option = new Option(time, time);
    startTimeSelect.add(option);
});

function calculateEndTime() {
    const startTime = document.getElementById('startTime').value;
    const duration = parseInt(document.getElementById('duration').value);

    if (startTime && duration) {
        let startMinutes = timeToMinutes(startTime);
        let endMinutes = startMinutes + duration;

        if (startMinutes < timeToMinutes("09:50") && endMinutes > timeToMinutes("09:50")) {
            endMinutes += 15;
        }

        if (startMinutes < timeToMinutes("15:30") && endMinutes > timeToMinutes("15:30")) {
            endMinutes += 15;
        }

        const endTime = findNextValidSlot(endMinutes);
        document.getElementById('endTime').textContent = endTime;
    } else {
        document.getElementById('endTime').textContent = '--:--';
    }
}

document.getElementById('startTime').addEventListener('change', calculateEndTime);
document.getElementById('duration').addEventListener('change', calculateEndTime);
