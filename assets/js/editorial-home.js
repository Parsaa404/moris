/**
 * Moris — Editorial Homepage Interactions
 * Controls calendar date selection, time slot picking, testimonial quote slider, and gallery controls.
 */

document.addEventListener('DOMContentLoaded', function () {
  // -------------------------------------------------------------------------
  // 1. Interactive Calendar & Booking
  // -------------------------------------------------------------------------
  const calDaysContainer = document.getElementById('ed-cal-days');
  const calMonthTitle = document.getElementById('ed-cal-month-title');
  const calPrevBtn = document.getElementById('ed-cal-prev');
  const calNextBtn = document.getElementById('ed-cal-next');
  const selectedDateInput = document.getElementById('ed-selected-date');
  const selectedSlotInput = document.getElementById('ed-selected-slot');
  const slotPills = document.querySelectorAll('.ed-slot-pill');

  let currentDate = new Date();
  // Ensure we display current month or future
  let currentMonth = currentDate.getMonth();
  let currentYear = currentDate.getFullYear();
  let selectedDateString = '';

  const monthNamesEn = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
  ];

  const monthNamesFa = [
    'ژانویه', 'فوریه', 'مارس', 'آوریل', 'مه', 'ژوئن',
    'ژوئیه', 'اوت', 'سپتامبر', 'اکتبر', 'نوامبر', 'دسامبر'
  ];

  const isRtl = document.documentElement.getAttribute('dir') === 'rtl';

  function renderCalendar(year, month) {
    if (!calDaysContainer || !calMonthTitle) return;

    calDaysContainer.innerHTML = '';
    const monthNames = isRtl ? monthNamesFa : monthNamesEn;
    calMonthTitle.textContent = `${monthNames[month]} ${year}`;

    // First day of month (0 = Sun, 1 = Mon ... 6 = Sat)
    // Adjust so Monday is day 0, Sunday is day 6
    const firstDay = new Date(year, month, 1);
    let startingDay = firstDay.getDay() - 1;
    if (startingDay < 0) startingDay = 6;

    const totalDays = new Date(year, month + 1, 0).getDate();
    const today = new Date();

    // Empty lead cells
    for (let i = 0; i < startingDay; i++) {
      const emptyCell = document.createElement('div');
      emptyCell.className = 'ed-cal-day is-disabled';
      calDaysContainer.appendChild(emptyCell);
    }

    // Days of month
    for (let day = 1; day <= totalDays; day++) {
      const dayBtn = document.createElement('button');
      dayBtn.type = 'button';
      dayBtn.className = 'ed-cal-day';
      dayBtn.textContent = day;

      const cellDate = new Date(year, month, day);
      const isPast = cellDate.setHours(0,0,0,0) < today.setHours(0,0,0,0);
      const isToday = (
        day === today.getDate() &&
        month === today.getMonth() &&
        year === today.getFullYear()
      );

      if (isToday) {
        dayBtn.classList.add('is-today');
      }

      if (isPast) {
        dayBtn.classList.add('is-disabled');
      } else {
        const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        dayBtn.dataset.date = dateStr;

        if (selectedDateString === dateStr) {
          dayBtn.classList.add('is-selected');
        }

        dayBtn.addEventListener('click', function () {
          document.querySelectorAll('.ed-cal-day').forEach(b => b.classList.remove('is-selected'));
          dayBtn.classList.add('is-selected');
          selectedDateString = dateStr;
          if (selectedDateInput) selectedDateInput.value = dateStr;
        });
      }

      calDaysContainer.appendChild(dayBtn);
    }

    // Auto-select first available day if none selected
    if (!selectedDateString) {
      const firstAvailable = calDaysContainer.querySelector('.ed-cal-day:not(.is-disabled)');
      if (firstAvailable) {
        firstAvailable.click();
      }
    }
  }

  if (calPrevBtn && calNextBtn) {
    calPrevBtn.addEventListener('click', function () {
      currentMonth--;
      if (currentMonth < 0) {
        currentMonth = 11;
        currentYear--;
      }
      renderCalendar(currentYear, currentMonth);
    });

    calNextBtn.addEventListener('click', function () {
      currentMonth++;
      if (currentMonth > 11) {
        currentMonth = 0;
        currentYear++;
      }
      renderCalendar(currentYear, currentMonth);
    });

    renderCalendar(currentYear, currentMonth);
  }

  // Time Slot Selection
  slotPills.forEach(function (pill) {
    pill.addEventListener('click', function () {
      slotPills.forEach(p => p.classList.remove('is-selected'));
      pill.classList.add('is-selected');
      if (selectedSlotInput) {
        selectedSlotInput.value = pill.dataset.time || pill.textContent.trim();
      }
    });
  });

  // -------------------------------------------------------------------------
  // 2. Testimonial Quote Switcher in Bio Section
  // -------------------------------------------------------------------------
  const quotes = [
    {
      text: "“Professional and utilizes a scientific approach to training.”",
      author: "Henry James — Executive & Amateur Boxer"
    },
    {
      text: "“Mori completely restructured my movement mechanics and eliminated chronic shoulder pain while adding 15kg to my bench.”",
      author: "Marcus Chen — Strength Athlete"
    },
    {
      text: "“The sharpest boxing coach in Kuala Lumpur. Real footwork, real discipline, real results.”",
      author: "David Lee — Boxing Client"
    }
  ];

  const bioQuoteText = document.getElementById('ed-bio-quote-text');
  const bioQuoteAuthor = document.getElementById('ed-bio-quote-author');
  const bioDots = document.querySelectorAll('.ed-bio-dot');

  let currentQuoteIdx = 0;

  function setQuote(idx) {
    if (!bioQuoteText || !bioQuoteAuthor) return;
    currentQuoteIdx = idx % quotes.length;
    bioQuoteText.textContent = quotes[currentQuoteIdx].text;
    bioQuoteAuthor.textContent = quotes[currentQuoteIdx].author;
    bioDots.forEach((dot, i) => {
      dot.classList.toggle('is-active', i === currentQuoteIdx);
    });
  }

  bioDots.forEach((dot, i) => {
    dot.addEventListener('click', () => setQuote(i));
  });

  // Optional auto-rotate quotes every 6 seconds
  setInterval(() => {
    if (bioQuoteText) {
      setQuote(currentQuoteIdx + 1);
    }
  }, 6500);

  // -------------------------------------------------------------------------
  // 3. Gallery Card Navigation & Dots
  // -------------------------------------------------------------------------
  const galleryDots = document.querySelectorAll('.ed-gallery-dot');
  const galleryPrev = document.getElementById('ed-gallery-prev');
  const galleryNext = document.getElementById('ed-gallery-next');
  const galleryCards = document.querySelectorAll('.ed-gallery-card');

  let activeGalleryPage = 0;

  function updateGallery(page) {
    activeGalleryPage = page;
    galleryDots.forEach((d, i) => d.classList.toggle('is-active', i === page));
    // Provide a subtle highlight or scale animation
    galleryCards.forEach(card => {
      card.style.transform = 'translateY(-4px)';
      setTimeout(() => { card.style.transform = ''; }, 300);
    });
  }

  galleryDots.forEach((d, idx) => {
    d.addEventListener('click', () => updateGallery(idx));
  });

  if (galleryPrev) {
    galleryPrev.addEventListener('click', () => {
      let next = activeGalleryPage - 1;
      if (next < 0) next = galleryDots.length - 1;
      updateGallery(next);
    });
  }

  if (galleryNext) {
    galleryNext.addEventListener('click', () => {
      let next = (activeGalleryPage + 1) % (galleryDots.length || 1);
      updateGallery(next);
    });
  }
});
