function getCookie(cname) {
    let name = cname + "=";
    let decodedCookie = decodeURIComponent(document.cookie);
    let ca = decodedCookie.split(';');
    for(let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

let themes = {
    "daylight": {
        background: "white",
        foreground: "black",
        platformbg: "#00d4ff",
        oddrow: "#a5c6f3",
        evenrow: "#d2e2f9",
        released: "#0a0",
        link: "#000"
    },
    "midnight": {
        background: "black",
        foreground: "white",
        platformbg: "#00a",
        oddrow: "#262a2b",
        evenrow: "#004daa",
        released: "#0f0",
        link: "white"
    },
    "nord": {
        background: "#2e3440",
        foreground: "#e5e9f0",
        platformbg: "#3b4252",
        oddrow: "#434c5e",
        evenrow: "#4c566a",
        released: "#0c0",
        link: "#e5e9f0"
    }
};

let currentTheme = getCookie("ghTheme");
let hideAddGame = getCookie("hideAddGame");
let searchEngine = getCookie("searchEngine");
let searchEngineCustomURL = getCookie("searchEngineCustomURL");

let searchEngines = {
    "startpage": "https://www.startpage.com/sp/search?query=%s",
    "ddg": "https://duckduckgo.com/?q=%s",
    "google": "https://www.google.com/search?q=%s",
    "bing": "https://www.bing.com/search?q=%s",
    "yandex": "https://yandex.com/search/?text=%s",
    "custom": searchEngineCustomURL
};

function searchEngineHandler() {
    if(document.getElementById("searchEngine").value == "custom") {
        document.getElementById("customSearchEngineBox").style.display = "block";
    } else {
        document.getElementById("customSearchEngineBox").style.display = "none";
    }
}

function saveSettings() {
    if(document.getElementById("hideAddGameCheckbox").checked) {
        hideAddGame = 1;
    } else {
        hideAddGame = 0;
    }

    searchEngine = document.getElementById("searchEngine").value;
    searchEngineCustomURL = document.getElementById("customSearchEngineBox").value;
    searchEngines.custom = searchEngineCustomURL;

    let d = new Date();
    d.setTime(d.getTime() + (3650*24*60*60*1000));
    let expires = "expires="+ d.toUTCString();
    document.cookie = "ghTheme=" + currentTheme + ";" + expires + ";SameSite=Lax;path=/";
    document.cookie = "hideAddGame=" + hideAddGame + ";" + expires + ";SameSite=Lax;path=/";
    document.cookie = "searchEngine=" + searchEngine + ";" + expires + ";SameSite=Lax;path=/";
    document.cookie = "searchEngineCustomURL=" + encodeURIComponent(searchEngineCustomURL) + ";" + expires + ";SameSite=Lax;path=/";

    applySearchEngine();
}

function doSearch(gameName) {
    if(searchEngines[searchEngine].includes("%s")) {
        window.open(searchEngines[searchEngine].replace("%s", gameName), '_blank');
    } else {
        alert("Malformed custom search engine URL, it must contain %s to substitute search terms.");
    }
}

function filterTable(table, searchStr) {
    let rows = document.querySelectorAll("#" + table + "Tbody > tr");
    searchStr = searchStr.trim().toLowerCase();

    if(searchStr == undefined || searchStr == "" || searchStr == null) {
        for(let i = 0; i < rows.length; i++) {
            rows[i].style.display = "table-row";
        }
    } else {
        for(let i = 0; i < rows.length; i++) {
            if(i !== (rows.length - 1)) { // Ignore last element
                if(rows[i].querySelector("td:nth-child(1)").innerText.trim().toLowerCase().includes(searchStr)) {
                    // Search titles
                    rows[i].style.display = "table-row";
                } else {
                    // Search platforms
                    let platformLabels = rows[i].querySelectorAll("td > span.platformLabel");
                    let matched = false;
                    for(let j = 0; j < platformLabels.length; j++) {
                        if(platformLabels[j].innerText.trim().toLowerCase().includes(searchStr)) {
                            rows[i].style.display = "table-row";
                            matched = true;
                            break;
                        }
                    }

                    if(!matched) {
                        rows[i].style.display = "none";
                    }
                }
            }
        }
    }
}

function applyTheme(themeName) {
    currentTheme = themeName;
    document.getElementsByTagName('style')[0].innerHTML = `
        html,body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: ${themes[themeName].background};
            color: ${themes[themeName].foreground};
        }

        #everything {
            width: 50%;
            margin: auto;
            margin-top: 50px;
        }

        #modal {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 600px;
            height: 260px;
            background-color: ${themes[themeName].background};
            padding: 20px;
            border: 1px solid ${themes[themeName].foreground};
            border-radius: 20px;
            box-shadow: 10px 10px 5px #888888;
        }

        #modalbg {
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1040;
            width: 100vw;
            height: 100vh;
            background-color: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(15px);
        }

        #newGameWrapper, #optionsWrapper, #updateWrapper {
            border: 1px solid ${themes[themeName].foreground};
            border-radius: 10px;
            padding: 10px;
            margin-bottom: 20px;
        }

        #updateWrapper {
            margin-top: 20px;
        }

        #newGameWrapper {
            display: ${hideAddGame == 1 ? "none" : "block"};
        }

        thead {
            position: sticky;
            top: 0px;
        }

        h1 {
            margin: 0;
        }

        input {
            background-color: ${themes[themeName].background};
            color: ${themes[themeName].foreground};
        }

        span.tab {
            margin-left: 5px;
            margin-right: 5px;
            cursor: pointer;
        }

        .pointer {
            cursor: pointer;
        }

        .gameReleased {
            color: ${themes[themeName].released};
            font-weight: bold;
        }

        span.activetab {
            font-weight: bold;
            text-decoration: underline;
        }

        div.searchbox {
            float: right;
            margin-bottom: 10px;
        }

        span.clearSearch {
            font-size: 0.6em;
        }

        input[type=search] {
            width: 10em;
            border: 1px solid ${themes[themeName].foreground};
            border-radius: 10px;
            padding: 5px;
        }

        h1 > a {
            text-decoration: none;
            color: ${themes[themeName].link};
        }

        a {
            color: ${themes[themeName].link};
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        span.platformLabel {
            border: 1px solid ${themes[themeName].platformbg};
            background-color: ${themes[themeName].platformbg};
            border-radius: 10px;
            color: ${themes[themeName].foreground};
            font-weight: bold;
            margin-left: 2px;
            margin-right: 2px;
            padding: 3px;
        }

        tr:nth-child(odd) {
            background: ${themes[themeName].oddrow};
        }

        tr:nth-child(even) {
            background: ${themes[themeName].evenrow};
        }

        #newEntryToggleDiv, #optionsToggleDiv {
            text-decoration: underline;
            cursor: pointer;
        }

        .editButton, .deleteButton {
            cursor: pointer;
        }

        .tableFooter {
            width: 100%;
            text-align: right;
        }

        #formHeader, #optionsHeader {
            font-size: 22px;
            font-weight: bold;
            margin-right: 10px;
        }

        th {
            text-align: left;
        }

        td,th {
            padding: 6px;
            border-bottom: 1px solid ${themes[themeName].background};
        }

        th {
            background-color: ${themes[themeName].evenrow};
        }

        .tableRounder {
            border-radius: 10px; 
            overflow: clip;
        }

        #tbaWrapper, #releasedWrapper, #collectionWrapper {
            display: none;
        }

        #customSearchEngineBox {
            margin-top: 5px;
            width: 30em;
            display: none;
        }

        #footer {
            width: 98%;
            margin-top: 20px;
            margin-bottom: 20px;
            font-size: smaller;
            text-align: center;
        }

        /* Phone styles */
        @media all and (max-width: 1000px) {
            #everything {
                width: 100%;
                margin: auto;
            }
        }
    </style>`;
}

function applySearchEngine() {
    let searchBtns = document.querySelectorAll(".searchButton");
    for(let i = 0; i < searchBtns.length; i++) {
        let gameTitle = searchBtns[i].closest("tr").firstElementChild.innerText.trim();
        searchBtns[i].href = searchEngines[searchEngine].replace("%s", gameTitle);
        searchBtns[i].title = `Search the web for ${gameTitle}`;
    }
}

if(!themes[currentTheme]) {
    currentTheme = "nord";
}

if(isNaN(hideAddGame) || hideAddGame == undefined || hideAddGame == null || hideAddGame.trim() == "") {
    hideAddGame = 0;
}

if(searchEngine == undefined || searchEngine == null || searchEngine.trim() == "") {
    searchEngine = "startpage";
}

applyTheme(currentTheme);
