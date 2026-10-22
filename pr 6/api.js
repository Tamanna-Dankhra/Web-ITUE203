// const DATA_URL = "data.json";
// // purpose of this file 
// // Fetch data from JSON file
// async function fetchData() {
//     try {
//         const response = await fetch(DATA_URL);

//         if (!response.ok) {
//             throw new Error("Failed to load JSON data");
//         }

//         const data = await response.json();
//         return data;
//     } 
//     catch (error) {
//         console.error("Error fetching data:", error);
//         throw error;
//     }
// }

// // Export function so app.js can use it
// export { fetchData };

// api.js

async function fetchData() {

    try {

        const response = await fetch("data.json");

        if (!response.ok) {
            throw new Error("Unable to load data");
        }

        const data = await response.json();

        return data;

    } catch (error) {

        throw error;

    }
}