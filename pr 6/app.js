// import { fetchData } from "./api.js";

// let data = {};
// let events = [];

// let currentPage = 1;
// const itemsPerPage = 4;


// // ===============================
// // DOM ELEMENTS
// // ===============================

// const eventContainer = document.getElementById("eventContainer");
// const studentContainer = document.getElementById("studentContainer");
// const noticeContainer = document.getElementById("noticeContainer");
// const faqContainer = document.getElementById("faqContainer");

// const searchInput = document.getElementById("searchInput");
// const categoryFilter = document.getElementById("categoryFilter");
// const sortSelect = document.getElementById("sortSelect");

// const pagination = document.getElementById("pagination");

// const loading = document.getElementById("loading");
// const errorMessage = document.getElementById("errorMessage");


// // ===============================
// // LOAD DATA
// // ===============================

// async function loadData() {

//     loading.style.display = "block";
//     errorMessage.style.display = "none";

//     try {
//         data = await fetchData();

//         events = data.events;

//         displayEvents();
//         displayStudents();
//         displayNotices();
//         displayFAQs();

//     } catch (error) {

//         errorMessage.textContent =
//             "Unable to load data. Please check the JSON file.";

//         errorMessage.style.display = "block";

//     } finally {

//         loading.style.display = "none";
//     }
// }


// // ===============================
// // DISPLAY EVENTS
// // ===============================

// function displayEvents() {

//     let filteredEvents = [...events];

//     const searchText = searchInput.value.toLowerCase();
//     const selectedCategory = categoryFilter.value;
//     const sortValue = sortSelect.value;


//     // SEARCH
//     if (searchText !== "") {

//         filteredEvents = filteredEvents.filter(event =>
//             event.title.toLowerCase().includes(searchText) ||
//             event.category.toLowerCase().includes(searchText) ||
//             event.venue.toLowerCase().includes(searchText)
//         );
//     }


//     // FILTER
//     if (selectedCategory !== "All") {

//         filteredEvents = filteredEvents.filter(event =>
//             event.category === selectedCategory
//         );
//     }


//     // SORT
//     if (sortValue === "dateAsc") {

//         filteredEvents.sort((a, b) =>
//             new Date(a.date) - new Date(b.date)
//         );

//     } else if (sortValue === "dateDesc") {

//         filteredEvents.sort((a, b) =>
//             new Date(b.date) - new Date(a.date)
//         );

//     } else if (sortValue === "nameAsc") {

//         filteredEvents.sort((a, b) =>
//             a.title.localeCompare(b.title)
//         );
//     }


//     // PAGINATION
//     const totalPages =
//         Math.ceil(filteredEvents.length / itemsPerPage);

//     if (currentPage > totalPages && totalPages > 0) {
//         currentPage = totalPages;
//     }

//     const startIndex =
//         (currentPage - 1) * itemsPerPage;

//     const endIndex =
//         startIndex + itemsPerPage;

//     const pageEvents =
//         filteredEvents.slice(startIndex, endIndex);


//     // CLEAR OLD EVENTS
//     eventContainer.innerHTML = "";


//     // NO RESULTS
//     if (pageEvents.length === 0) {

//         eventContainer.innerHTML =
//             "<p class='no-results'>No events found.</p>";

//         pagination.innerHTML = "";

//         return;
//     }


//     // CREATE EVENT CARDS
//     pageEvents.forEach(event => {

//         const card = document.createElement("div");

//         card.className = "event-card";

//         card.innerHTML = `
//             <div class="event-category">
//                 ${event.category}
//             </div>

//             <h3>${event.title}</h3>

//             <p>${event.description}</p>

//             <div class="event-info">
//                 <span>📅 ${formatDate(event.date)}</span>
//                 <span>📍 ${event.venue}</span>
//             </div>
//         `;

//         eventContainer.appendChild(card);
//     });


//     createPagination(totalPages);
// }


// // ===============================
// // PAGINATION
// // ===============================

// function createPagination(totalPages) {

//     pagination.innerHTML = "";

//     if (totalPages <= 1) {
//         return;
//     }


//     // PREVIOUS BUTTON

//     const previousButton =
//         document.createElement("button");

//     previousButton.textContent = "Previous";

//     previousButton.disabled = currentPage === 1;

//     previousButton.addEventListener("click", () => {

//         currentPage--;

//         displayEvents();
//     });

//     pagination.appendChild(previousButton);


//     // PAGE NUMBERS

//     for (let i = 1; i <= totalPages; i++) {

//         const button =
//             document.createElement("button");

//         button.textContent = i;

//         if (i === currentPage) {
//             button.classList.add("active");
//         }

//         button.addEventListener("click", () => {

//             currentPage = i;

//             displayEvents();
//         });

//         pagination.appendChild(button);
//     }


//     // NEXT BUTTON

//     const nextButton =
//         document.createElement("button");

//     nextButton.textContent = "Next";

//     nextButton.disabled =
//         currentPage === totalPages;

//     nextButton.addEventListener("click", () => {

//         currentPage++;

//         displayEvents();
//     });

//     pagination.appendChild(nextButton);
// }


// // ===============================
// // DISPLAY STUDENTS
// // ===============================

// function displayStudents() {

//     studentContainer.innerHTML = "";

//     data.students.forEach(student => {

//         const card =
//             document.createElement("div");

//         card.className = "student-card";

//         card.innerHTML = `
//             <div class="student-icon">
//                 ${student.name.charAt(0)}
//             </div>

//             <h3>${student.name}</h3>

//             <p><strong>ID:</strong> ${student.id}</p>

//             <p><strong>Branch:</strong> ${student.branch}</p>

//             <p><strong>Semester:</strong> ${student.semester}</p>

//             <p><strong>Email:</strong> ${student.email}</p>
//         `;

//         studentContainer.appendChild(card);
//     });
// }


// // ===============================
// // DISPLAY NOTICES
// // ===============================

// function displayNotices() {

//     noticeContainer.innerHTML = "";

//     data.notices.forEach(notice => {

//         const noticeBox =
//             document.createElement("div");

//         noticeBox.className = "notice";

//         noticeBox.innerHTML = `
//             <div>
//                 <h3>${notice.title}</h3>
//                 <p>${notice.message}</p>
//             </div>

//             <span>${formatDate(notice.date)}</span>
//         `;

//         noticeContainer.appendChild(noticeBox);
//     });
// }


// // ===============================
// // DISPLAY FAQs
// // ===============================

// function displayFAQs() {

//     faqContainer.innerHTML = "";

//     data.faqs.forEach(faq => {

//         const faqBox =
//             document.createElement("div");

//         faqBox.className = "faq";

//         faqBox.innerHTML = `
//             <h3>${faq.question}</h3>
//             <p>${faq.answer}</p>
//         `;

//         faqContainer.appendChild(faqBox);
//     });
// }


// // ===============================
// // FORMAT DATE
// // ===============================

// function formatDate(date) {

//     return new Date(date).toLocaleDateString(
//         "en-IN",
//         {
//             day: "2-digit",
//             month: "short",
//             year: "numeric"
//         }
//     );
// }


// // ===============================
// // SEARCH EVENT
// // ===============================

// searchInput.addEventListener("input", () => {

//     currentPage = 1;

//     displayEvents();
// });


// // ===============================
// // FILTER EVENT
// // ===============================

// categoryFilter.addEventListener("change", () => {

//     currentPage = 1;

//     displayEvents();
// });


// // ===============================
// // SORT EVENT
// // ===============================

// sortSelect.addEventListener("change", () => {

//     currentPage = 1;

//     displayEvents();
// });


// // ===============================
// // INITIALIZE APPLICATION
// // ===============================

// loadData();

// app.js

let data = {};

let currentSection = "events";

let currentPage = 1;

const recordsPerPage = 6;


/* =========================================
   LOAD DATA
========================================= */

async function loadData() {

    const loading = document.getElementById("loading");
    const error = document.getElementById("error");

    loading.style.display = "block";
    error.textContent = "";

    try {

        data = await fetchData();

        loading.style.display = "none";

        showSection("events");

        populateFilter();

        render();

    } catch (err) {

        loading.style.display = "none";

        error.textContent =
            "Unable to load data. Please check the JSON file.";

        console.error(err);
    }
}


/* =========================================
   SHOW SECTION
========================================= */

function showSection(section) {

    currentSection = section;

    currentPage = 1;

    document.getElementById("eventsSection").style.display =
        section === "events" ? "block" : "none";

    document.getElementById("studentsSection").style.display =
        section === "students" ? "block" : "none";

    document.getElementById("noticesSection").style.display =
        section === "notices" ? "block" : "none";

    document.getElementById("faqsSection").style.display =
        section === "faqs" ? "block" : "none";

    populateFilter();

    render();
}


/* =========================================
   GET CURRENT DATA
========================================= */

function getCurrentData() {

    return data[currentSection] || [];

}


/* =========================================
   FILTER DROPDOWN
========================================= */

function populateFilter() {

    const filter = document.getElementById("filterSelect");

    filter.innerHTML =
        '<option value="all">All Categories</option>';

    const records = getCurrentData();

    let categories = [];

    records.forEach(item => {

        let category;

        if (currentSection === "events") {
            category = item.category;
        }

        else if (currentSection === "students") {
            category = item.course;
        }

        else if (currentSection === "notices") {
            category = item.category;
        }

        if (category && !categories.includes(category)) {
            categories.push(category);
        }

    });

    categories.sort();

    categories.forEach(category => {

        const option = document.createElement("option");

        option.value = category;

        option.textContent = category;

        filter.appendChild(option);

    });
}


/* =========================================
   GET FILTERED DATA
========================================= */

function getFilteredData() {

    const searchText =
        document.getElementById("searchInput")
        .value
        .toLowerCase();

    const filterValue =
        document.getElementById("filterSelect").value;

    const sortValue =
        document.getElementById("sortSelect").value;


    let records = getCurrentData();


    /* SEARCH */

    records = records.filter(item => {

        return JSON.stringify(item)
            .toLowerCase()
            .includes(searchText);

    });


    /* FILTER */

    if (filterValue !== "all") {

        records = records.filter(item => {

            if (currentSection === "students") {

                return item.course === filterValue;

            }

            return item.category === filterValue;

        });

    }


    /* SORT */

    if (sortValue === "asc") {

        records.sort((a, b) => {

            return getTitle(a)
                .localeCompare(getTitle(b));

        });

    }

    else if (sortValue === "desc") {

        records.sort((a, b) => {

            return getTitle(b)
                .localeCompare(getTitle(a));

        });

    }


    return records;
}


/* =========================================
   GET TITLE
========================================= */

function getTitle(item) {

    if (item.title) {
        return item.title;
    }

    if (item.name) {
        return item.name;
    }

    if (item.question) {
        return item.question;
    }

    return "";

}


/* =========================================
   RENDER
========================================= */

function render() {

    const records = getFilteredData();

    const start =
        (currentPage - 1) * recordsPerPage;

    const end =
        start + recordsPerPage;

    const pageRecords =
        records.slice(start, end);


    if (currentSection === "events") {
        renderEvents(pageRecords);
    }

    else if (currentSection === "students") {
        renderStudents(pageRecords);
    }

    else if (currentSection === "notices") {
        renderNotices(pageRecords);
    }

    else if (currentSection === "faqs") {
        renderFAQs(pageRecords);
    }


    updatePagination(records.length);
}


/* =========================================
   RENDER EVENTS
========================================= */

function renderEvents(events) {

    const container =
        document.getElementById("eventContainer");

    container.innerHTML = "";


    events.forEach(event => {

        const card =
            document.createElement("div");

        card.className = "event";

        card.innerHTML = `
            <h3>${event.title}</h3>

            <p>
                <strong>Category:</strong>
                ${event.category}
            </p>

            <p>
                <strong>Date:</strong>
                ${event.date}
            </p>

            <p>
                <strong>Location:</strong>
                ${event.location}
            </p>
        `;

        container.appendChild(card);

    });
}


/* =========================================
   RENDER STUDENTS
========================================= */

function renderStudents(students) {

    const container =
        document.getElementById("studentContainer");

    container.innerHTML = "";


    students.forEach(student => {

        const card =
            document.createElement("div");

        card.className = "student";

        card.innerHTML = `
            <h3>${student.name}</h3>

            <p>
                <strong>Course:</strong>
                ${student.course}
            </p>

            <p>
                <strong>Year:</strong>
                ${student.year}
            </p>
        `;

        container.appendChild(card);

    });
}


/* =========================================
   RENDER NOTICES
========================================= */

function renderNotices(notices) {

    const container =
        document.getElementById("noticeContainer");

    container.innerHTML = "";


    notices.forEach(notice => {

        const card =
            document.createElement("div");

        card.className = "notice";

        card.innerHTML = `
            <h3>${notice.title}</h3>

            <p>
                <strong>Category:</strong>
                ${notice.category}
            </p>

            <p>
                <strong>Date:</strong>
                ${notice.date}
            </p>
        `;

        container.appendChild(card);

    });
}


/* =========================================
   RENDER FAQ
========================================= */

function renderFAQs(faqs) {

    const container =
        document.getElementById("faqContainer");

    container.innerHTML = "";


    faqs.forEach(faq => {

        const card =
            document.createElement("div");

        card.className = "faq";

        card.innerHTML = `
            <h3>${faq.question}</h3>

            <p>
                ${faq.answer}
            </p>
        `;

        container.appendChild(card);

    });
}


/* =========================================
   PAGINATION
========================================= */

function updatePagination(totalRecords) {

    const totalPages =
        Math.ceil(totalRecords / recordsPerPage);


    document.getElementById("pageInfo").textContent =
        `Page ${currentPage} of ${totalPages || 1}`;


    document.getElementById("prevBtn").disabled =
        currentPage === 1;


    document.getElementById("nextBtn").disabled =
        currentPage >= totalPages;

}


/* =========================================
   PREVIOUS PAGE
========================================= */

document.getElementById("prevBtn")
    .addEventListener("click", function () {

        if (currentPage > 1) {

            currentPage--;

            render();

        }

    });


/* =========================================
   NEXT PAGE
========================================= */

document.getElementById("nextBtn")
    .addEventListener("click", function () {

        const totalRecords =
            getFilteredData().length;

        const totalPages =
            Math.ceil(
                totalRecords / recordsPerPage
            );


        if (currentPage < totalPages) {

            currentPage++;

            render();

        }

    });


/* =========================================
   SEARCH
========================================= */

document.getElementById("searchInput")
    .addEventListener("input", function () {

        currentPage = 1;

        render();

    });


/* =========================================
   FILTER
========================================= */

document.getElementById("filterSelect")
    .addEventListener("change", function () {

        currentPage = 1;

        render();

    });


/* =========================================
   SORT
========================================= */

document.getElementById("sortSelect")
    .addEventListener("change", function () {

        currentPage = 1;

        render();

    });


/* =========================================
   START APPLICATION
========================================= */

loadData();