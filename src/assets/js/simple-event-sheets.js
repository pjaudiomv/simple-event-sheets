const CONFIG = {
  get URL() {
    return `https://sheets.googleapis.com/v4/spreadsheets/${simpleEventSheetsParams.SHEET_ID}/values/${simpleEventSheetsParams.SHEET_NAME}?key=${simpleEventSheetsParams.API_KEY}`;
  },
  SHOW_PASSED_EVENTS: simpleEventSheetsParams.SHOW_PASSED_EVENTS ?? false,
  DEFAULT_VIEW: simpleEventSheetsParams.DEFAULT_VIEW ?? 'list',
  SHOW_TOGGLE: simpleEventSheetsParams.SHOW_TOGGLE ?? 'true',
  CALENDAR_START_DAY: simpleEventSheetsParams.CALENDAR_START_DAY ?? 'sunday',
  MAX_EVENTS_PER_DAY: parseInt(simpleEventSheetsParams.MAX_EVENTS_PER_DAY) || 3,
};

// Global variables
let currentCalendarDate = new Date();
let calendarEvents = [];
let currentView = CONFIG.DEFAULT_VIEW;

const convertSheetDataToListOfDicts = (sheetData) => {
  const headers = sheetData.values?.[0] || [];
  const rows = sheetData.values?.slice(1) || [];
  return rows.map((row) =>
    Object.fromEntries(row.map((cell, index) => [headers[index], cell]))
  );
};

const parseDate = (dateStr) => {
  const [month, day, year] = dateStr.split("/").map(Number);
  return {month, day, year};
};

const isValidDate = (dateStr) => {
  const parsed = parseDate(dateStr);
  const date = new Date(parsed.year, parsed.month - 1, parsed.day);
  return (
    date.getFullYear() === parsed.year &&
    date.getMonth() === parsed.month - 1 &&
    date.getDate() === parsed.day
  );
};

const isValidURL = (str) => {
  try {
    new URL(str);
    return true;
  } catch (_) {
    return false;
  }
};

const getMonthNameFromNumber = (monthNumber) =>
  new Date(0, monthNumber - 1).toLocaleString("default", {month: "long"});

const ordinalSuffix = (num) => {
  const j = num % 10,
    k = num % 100;
  if (j === 1 && k !== 11) return `${num}<sup>st</sup>`;
  if (j === 2 && k !== 12) return `${num}<sup>nd</sup>`;
  if (j === 3 && k !== 13) return `${num}<sup>rd</sup>`;
  return `${num}<sup>th</sup>`;
};

const generateEventName = (event) => {
  if (event.url?.trim() && isValidURL(event.url)) {
    return `<a href="${event.url}" target="_blank">${event.name}</a>${
      event.event_info ? ` (${event.event_info})` : ""
    }`;
  } else {
    return event.event_info
      ? `${event.name} (${event.event_info})`
      : event.name;
  }
};

// Calendar utility functions
const getDaysInMonth = (year, month) => {
  return new Date(year, month + 1, 0).getDate();
};

const getFirstDayOfMonth = (year, month) => {
  return new Date(year, month, 1).getDay();
};

const formatCalendarTitle = (year, month) => {
  const monthNames = ["January", "February", "March", "April", "May", "June",
    "July", "August", "September", "October", "November", "December"];
  return `${monthNames[month]} ${year}`;
};

const getEventsForDate = (year, month, day) => {
  const targetDate = `${month + 1}/${day}/${year}`;
  return calendarEvents.filter(event => event.date === targetDate);
};

const isToday = (year, month, day) => {
  const today = new Date();
  return year === today.getFullYear() &&
    month === today.getMonth() &&
    day === today.getDate();
};

const getCalendarStartDay = () => {
  // Get start day from CONFIG (default to Sunday = 0)
  return CONFIG.CALENDAR_START_DAY === 'monday' ? 1 : 0;
};

const getMaxEventsPerDay = () => {
  return CONFIG.MAX_EVENTS_PER_DAY;
};

// Initialize containers with configuration-aware setup
const initializeContainers = () => {
  const container = document.getElementById("simple-event-sheets-container");
  if (!container) return;

  // Get configuration from container attributes or CONFIG
  const defaultView = container.dataset.defaultView || CONFIG.DEFAULT_VIEW;
  const showToggle = container.dataset.showToggle || CONFIG.SHOW_TOGGLE;

  // Update current view based on configuration
  currentView = defaultView;

  // Create view toggle (only if enabled)
  let viewToggleHTML = '';
  if (showToggle === 'true') {
    const listActive = defaultView === 'list' ? 'active' : '';
    const calendarActive = defaultView === 'calendar' ? 'active' : '';

    viewToggleHTML = `
      <div class="simple-events-view-toggle">
        <button id="simple-events-list-btn" class="view-btn ${listActive}">List View</button>
        <button id="simple-events-calendar-btn" class="view-btn ${calendarActive}">Calendar View</button>
      </div>
    `;
  }

  // Create calendar container
  const calendarDisplay = defaultView === 'calendar' ? 'block' : 'none';
  const calendarHTML = `
    <div class="simple-events-calendar-container" style="display: ${calendarDisplay};">
      <div class="simple-events-calendar-header">
        <div class="simple-events-calendar-nav">
          <button id="simple-events-prev-month">◀ Previous</button>
          <button id="simple-events-today-btn">Today</button>
          <button id="simple-events-next-month">Next ▶</button>
        </div>
        <h2 class="simple-events-calendar-title" id="simple-events-calendar-title">January 2025</h2>
      </div>
      <div class="simple-events-calendar-grid" id="simple-events-calendar-grid">
        <!-- Calendar will be populated by JavaScript -->
      </div>
    </div>
  `;

  // Create list container
  const listDisplay = defaultView === 'list' ? 'block' : 'none';
  const listHTML = `
    <div class="simple-events-list-container" id="simple-events-list-content" style="display: ${listDisplay};">
      <!-- List will be populated by JavaScript -->
    </div>
  `;

  // Create modal
  const modalHTML = `
    <div class="simple-events-modal" id="simple-events-modal">
      <div class="simple-events-modal-content">
        <div class="simple-events-modal-header">
          <h3 class="simple-events-modal-title" id="simple-events-modal-title">Event Details</h3>
          <button class="simple-events-modal-close" id="simple-events-modal-close">&times;</button>
        </div>
        <div id="simple-events-modal-content">
          <!-- Event details will be populated by JavaScript -->
        </div>
      </div>
    </div>
  `;

  // Replace container content
  container.innerHTML = viewToggleHTML + calendarHTML + listHTML + modalHTML;
};

// Calendar rendering functions with CONFIG integration
const renderCalendar = () => {
  const year = currentCalendarDate.getFullYear();
  const month = currentCalendarDate.getMonth();

  const titleElement = document.getElementById('simple-events-calendar-title');
  if (titleElement) {
    titleElement.textContent = formatCalendarTitle(year, month);
  }

  const calendarGrid = document.getElementById('simple-events-calendar-grid');
  if (!calendarGrid) return;

  calendarGrid.innerHTML = '';

  // Day headers (adjust based on CONFIG start day setting)
  const startDay = getCalendarStartDay();
  const dayHeaders = startDay === 1
    ? ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']
    : ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

  dayHeaders.forEach(day => {
    const dayHeader = document.createElement('div');
    dayHeader.className = 'simple-events-calendar-day-header';
    dayHeader.textContent = day;
    calendarGrid.appendChild(dayHeader);
  });

  const firstDayOfMonth = getFirstDayOfMonth(year, month);
  const adjustedFirstDay = startDay === 1
    ? (firstDayOfMonth === 0 ? 6 : firstDayOfMonth - 1)
    : firstDayOfMonth;

  const daysInMonth = getDaysInMonth(year, month);
  const daysInPrevMonth = getDaysInMonth(year, month - 1);

  // Previous month's trailing days
  for (let i = adjustedFirstDay - 1; i >= 0; i--) {
    const day = daysInPrevMonth - i;
    const dayElement = createCalendarDay(year, month - 1, day, true);
    calendarGrid.appendChild(dayElement);
  }

  // Current month's days
  for (let day = 1; day <= daysInMonth; day++) {
    const dayElement = createCalendarDay(year, month, day, false);
    calendarGrid.appendChild(dayElement);
  }

  // Next month's leading days
  const totalCells = calendarGrid.children.length - 7; // Subtract header row
  const remainingCells = 42 - totalCells; // 6 rows × 7 days
  for (let day = 1; day <= remainingCells; day++) {
    const dayElement = createCalendarDay(year, month + 1, day, true);
    calendarGrid.appendChild(dayElement);
  }
};

const createCalendarDay = (year, month, day, isOtherMonth) => {
  const dayElement = document.createElement('div');
  dayElement.className = 'simple-events-calendar-day';

  if (isOtherMonth) {
    dayElement.classList.add('other-month');
  }

  if (isToday(year, month, day)) {
    dayElement.classList.add('today');
  }

  // Day number
  const dayNumber = document.createElement('div');
  dayNumber.className = 'simple-events-day-number';
  dayNumber.textContent = day;
  dayElement.appendChild(dayNumber);

  // Events for this day
  const eventsForDay = getEventsForDate(year, month, day);
  if (eventsForDay.length > 0) {
    dayElement.classList.add('has-events');

    const eventsContainer = document.createElement('div');
    eventsContainer.className = 'simple-events-calendar-events';

    // Use CONFIG max events setting
    const maxVisible = getMaxEventsPerDay();
    eventsForDay.slice(0, maxVisible).forEach(event => {
      const eventElement = document.createElement('div');
      eventElement.className = 'simple-events-calendar-event';
      if (eventsForDay.length > 1) {
        eventElement.classList.add('multi-event');
      }
      eventElement.textContent = event.name;
      eventElement.addEventListener('click', (e) => {
        e.stopPropagation();
        showEventModal(event);
      });
      eventsContainer.appendChild(eventElement);
    });

    if (eventsForDay.length > maxVisible) {
      const countElement = document.createElement('div');
      countElement.className = 'simple-events-event-count';
      countElement.textContent = `+${eventsForDay.length - maxVisible} more`;
      eventsContainer.appendChild(countElement);
    }

    dayElement.appendChild(eventsContainer);

    // Click handler for day
    dayElement.addEventListener('click', () => {
      showDayModal(year, month, day, eventsForDay);
    });
  }

  return dayElement;
};

// Modal functions
const showEventModal = (event) => {
  const modal = document.getElementById('simple-events-modal');
  const title = document.getElementById('simple-events-modal-title');
  const content = document.getElementById('simple-events-modal-content');

  if (!modal || !title || !content) return;

  title.textContent = event.name;

  const eventDate = parseDate(event.date);
  const formattedDate = `${getMonthNameFromNumber(eventDate.month)} ${eventDate.day}, ${eventDate.year}`;

  content.innerHTML = `
    <div class="simple-events-event-details">
      <p><strong>Date:</strong> ${formattedDate}</p>
      ${event.event_info ? `<p><strong>Info:</strong> ${event.event_info}</p>` : ''}
      ${event.day_info ? `<p><strong>Day Info:</strong> ${event.day_info}</p>` : ''}
      ${event.url && isValidURL(event.url) ? `<p><strong>Link:</strong> <a href="${event.url}" target="_blank" class="simple-events-event-link">View Details</a></p>` : ''}
    </div>
  `;

  modal.classList.add('active');
};

const showDayModal = (year, month, day, events) => {
  const modal = document.getElementById('simple-events-modal');
  const title = document.getElementById('simple-events-modal-title');
  const content = document.getElementById('simple-events-modal-content');

  if (!modal || !title || !content) return;

  const formattedDate = `${getMonthNameFromNumber(month + 1)} ${day}, ${year}`;
  title.textContent = `Events for ${formattedDate}`;

  let eventsHTML = '';
  events.forEach((event, index) => {
    eventsHTML += `
      <div class="simple-events-event-details">
        <h3>${event.name}</h3>
        ${event.event_info ? `<p><strong>Info:</strong> ${event.event_info}</p>` : ''}
        ${event.day_info ? `<p><strong>Day Info:</strong> ${event.day_info}</p>` : ''}
        ${event.url && isValidURL(event.url) ? `<p><strong>Link:</strong> <a href="${event.url}" target="_blank" class="simple-events-event-link">View Details</a></p>` : ''}
      </div>
    `;

    if (index < events.length - 1) {
      eventsHTML += '<hr class="simple-events-divider">';
    }
  });

  content.innerHTML = eventsHTML;
  modal.classList.add('active');
};

const displayEventsByMonth = (events) => {
  const currentYear = new Date().getFullYear();
  const validEvents = events.filter(
    (event) =>
      event.date &&
      event.name &&
      isValidDate(event.date.trim()) &&
      event.name.trim()
  );

  validEvents.sort((a, b) => {
    const dateA = parseDate(a.date);
    const dateB = parseDate(b.date);
    return (
      dateA.year - dateB.year ||
      dateA.month - dateB.month ||
      dateA.day - dateB.day
    );
  });

  const groupedEvents = validEvents.reduce((acc, event) => {
    let currentDate = new Date();
    let eventDate = new Date(event.date);
    if (!CONFIG.SHOW_PASSED_EVENTS && eventDate <= currentDate) return acc;
    const {month, year} = parseDate(event.date);
    const key = `${month} - ${year}`;
    acc[key] = acc[key] || [];
    acc[key].push(event);
    return acc;
  }, {});

  const eventGroups = document.getElementById("simple-events-list-content");
  if (!eventGroups) return;

  eventGroups.innerHTML = '';

  if (Object.keys(groupedEvents).length === 0) {
    eventGroups.textContent = "No upcoming events";
    return;
  }

  Object.keys(groupedEvents).forEach((monthYearKey) => {
    const [month, , year] = monthYearKey.split(" ");
    const monthDiv = document.createElement("div");
    const monthHeading = document.createElement("h2");
    monthHeading.textContent = `${getMonthNameFromNumber(month)} ${
      year > currentYear ? `- ${year}` : ""
    }`;
    monthDiv.appendChild(monthHeading);

    const daysOl = document.createElement("ol");
    daysOl.className = "circle-bullets";
    let previousEventDay = null;
    let processedInfoDays = new Set();

    groupedEvents[monthYearKey].forEach((event) => {
      const {day} = parseDate(event.date);
      const info = event.day_info?.trim() ? ` ${event.day_info}` : "";
      const eventName = generateEventName(event);

      if (previousEventDay !== day) {
        const dayLi = document.createElement("li");
        dayLi.innerHTML =
          !processedInfoDays.has(day) && info
            ? `${ordinalSuffix(day)} - ${info}`
            : ordinalSuffix(day);
        processedInfoDays.add(day);
        daysOl.appendChild(dayLi);

        const eventsOl = document.createElement("ol");
        eventsOl.className = "circle-bullets-inner";
        dayLi.appendChild(eventsOl);
        const eventLi = document.createElement("li");
        eventLi.innerHTML = eventName;
        eventsOl.appendChild(eventLi);
      } else {
        const eventsOl = daysOl.lastChild.lastChild;
        const eventLi = document.createElement("li");
        eventLi.innerHTML = eventName;
        eventsOl.appendChild(eventLi);
      }

      previousEventDay = day;
    });

    monthDiv.appendChild(daysOl);
    eventGroups.appendChild(monthDiv);
  });
};

const setupEventListeners = () => {
  // View toggle (only if toggle exists)
  const listBtn = document.getElementById('simple-events-list-btn');
  const calendarBtn = document.getElementById('simple-events-calendar-btn');
  const listView = document.querySelector('.simple-events-list-container');
  const calendarView = document.querySelector('.simple-events-calendar-container');

  if (listBtn && calendarBtn && listView && calendarView) {
    listBtn.addEventListener('click', () => {
      listBtn.classList.add('active');
      calendarBtn.classList.remove('active');
      listView.style.display = 'block';
      calendarView.style.display = 'none';
      currentView = 'list';
    });

    calendarBtn.addEventListener('click', () => {
      calendarBtn.classList.add('active');
      listBtn.classList.remove('active');
      calendarView.style.display = 'block';
      listView.style.display = 'none';
      currentView = 'calendar';
      renderCalendar();
    });
  }

  // Calendar navigation
  const prevBtn = document.getElementById('simple-events-prev-month');
  const nextBtn = document.getElementById('simple-events-next-month');
  const todayBtn = document.getElementById('simple-events-today-btn');

  if (prevBtn) {
    prevBtn.addEventListener('click', () => {
      currentCalendarDate.setMonth(currentCalendarDate.getMonth() - 1);
      renderCalendar();
    });
  }

  if (nextBtn) {
    nextBtn.addEventListener('click', () => {
      currentCalendarDate.setMonth(currentCalendarDate.getMonth() + 1);
      renderCalendar();
    });
  }

  if (todayBtn) {
    todayBtn.addEventListener('click', () => {
      currentCalendarDate = new Date();
      renderCalendar();
    });
  }

  // Modal close
  const modalClose = document.getElementById('simple-events-modal-close');
  const modal = document.getElementById('simple-events-modal');

  if (modalClose) {
    modalClose.addEventListener('click', () => {
      modal?.classList.remove('active');
    });
  }

  if (modal) {
    modal.addEventListener('click', (e) => {
      if (e.target.id === 'simple-events-modal') {
        modal.classList.remove('active');
      }
    });
  }
};

// Main initialization with CONFIG integration
document.addEventListener("DOMContentLoaded", function () {
  initializeContainers();

  if (simpleEventSheetsParams.EVENTS) {
    console.log("Loaded data server side");
    const events = convertSheetDataToListOfDicts(
      JSON.parse(simpleEventSheetsParams.EVENTS)
    );
    calendarEvents = events;
    displayEventsByMonth(events);

    // Render calendar if it's the default view from CONFIG
    if (currentView === 'calendar') {
      renderCalendar();
    }

    setupEventListeners();
  } else {
    fetch(CONFIG.URL)
      .then((response) => response.json())
      .then((data) => {
        console.log("Loaded data client side");
        const events = convertSheetDataToListOfDicts(data);
        calendarEvents = events;
        displayEventsByMonth(events);

        // Render calendar if it's the default view from CONFIG
        if (currentView === 'calendar') {
          renderCalendar();
        }

        setupEventListeners();
      })
      .catch((error) => console.error("Error fetching data:", error));
  }
});
