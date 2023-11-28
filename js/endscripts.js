document.getElementById("editModalOtherPlatforms").readOnly = true;
document.getElementById("addGameOtherPlatforms").readOnly = true;

document.getElementById("unreleased").onchange = function() {
    if(this.checked) {
        document.getElementById("releaseDate").readOnly = false;
    }
};

document.getElementById("tba").onchange = function() {
    if(this.checked) {
        document.getElementById("releaseDate").readOnly = true;
    }
};

document.getElementById("released").onchange = function() {
    if(this.checked) {
        document.getElementById("releaseDate").readOnly = true;
    }
};

document.getElementById("collection").onchange = function() {
    if(this.checked) {
        document.getElementById("releaseDate").readOnly = true;
    }
};

document.getElementById("platformCheckboxOther").onchange = function() {
    if(this.checked) {
        document.getElementById("addGameOtherPlatforms").readOnly = false;
    } else {
        document.getElementById("addGameOtherPlatforms").readOnly = true;
    }
};

document.getElementById("editModalUnreleased").onchange = function() {
    if(this.checked) {
        document.getElementById("editModalReleaseDate").readOnly = false;
    }
};

document.getElementById("editModalTBA").onchange = function() {
    if(this.checked) {
        document.getElementById("editModalReleaseDate").readOnly = true;
    }
};

document.getElementById("editModalReleased").onchange = function() {
    if(this.checked) {
        document.getElementById("editModalReleaseDate").readOnly = true;
    }
};

document.getElementById("editModalCollection").onchange = function() {
    if(this.checked) {
        document.getElementById("editModalReleaseDate").readOnly = true;
    }
};

document.getElementById("editModalCheckboxOther").onchange = function() {
    if(this.checked) {
        document.getElementById("editModalOtherPlatforms").readOnly = false;
    } else {
        document.getElementById("editModalOtherPlatforms").readOnly = true;
    }
};

function toggleNewGameDiv() {
    let display = document.getElementById("newGameWrapper").style.display;
    if(display == "none" || display == "") {
        document.getElementById("optionsWrapper").style.display = "none";
        document.getElementById("newGameWrapper").style.display = "block";
    } else {
        document.getElementById("newGameWrapper").style.display = "none";
    }
}

function toggleOptionsDiv() {
    if(document.getElementById("optionsWrapper").style.display == "none") {
        document.getElementById("newGameWrapper").style.display = "none";
        document.getElementById("optionsWrapper").style.display = "block";
    } else {
        document.getElementById("optionsWrapper").style.display = "none";
    }
}

function editGame(table, id, editBtn) {
    let row = editBtn.parentNode.parentNode;
    let gameName = row.firstElementChild.innerText.trim();
    let releaseDate = "";
    
    if(table == "unreleased") {
        releaseDate = row.firstElementChild.nextElementSibling.innerText.trim();
    }

    document.getElementById("editModalGameTitle").value = gameName;

    if(table == "unreleased") {
        document.getElementById("editModalUnreleased").checked = true;
        document.getElementById("editModalTBA").checked = false;
        document.getElementById("editModalReleased").checked = false;
        document.getElementById("editModalCollection").checked = false;
        document.getElementById("editModalReleaseDate").value = releaseDate;
        document.getElementById("editModalReleaseDate").readOnly = false;
    } else if(table == "tba") {
        document.getElementById("editModalUnreleased").checked = false;
        document.getElementById("editModalTBA").checked = true;
        document.getElementById("editModalReleased").checked = false;
        document.getElementById("editModalCollection").checked = false;
        document.getElementById("editModalReleaseDate").value = "0000-00-00";
        document.getElementById("editModalReleaseDate").readOnly = true;
    } else if(table == "released") {
        document.getElementById("editModalUnreleased").checked = false;
        document.getElementById("editModalTBA").checked = false;
        document.getElementById("editModalReleased").checked = true;
        document.getElementById("editModalCollection").checked = false;
        document.getElementById("editModalReleaseDate").value = "0000-00-00";
        document.getElementById("editModalReleaseDate").readOnly = true;
    } else if(table == "collection") {
        document.getElementById("editModalUnreleased").checked = false;
        document.getElementById("editModalTBA").checked = false;
        document.getElementById("editModalReleased").checked = false;
        document.getElementById("editModalCollection").checked = true;
        document.getElementById("editModalReleaseDate").value = "0000-00-00";
        document.getElementById("editModalReleaseDate").readOnly = true;
    }
    
    let platformNode = row.firstElementChild.nextElementSibling;
    
    if(table == "unreleased") {
        platformNode = row.firstElementChild.nextElementSibling.nextElementSibling;
    }

    let platformSpans = platformNode.querySelectorAll("span");
    let platforms = [];

    for(let i = 0; i < platformSpans.length; i++) {
        platforms.push(platformSpans[i].innerText.trim());
    }

    // Determine which defined platforms to pre-check
    let checkboxes = document.getElementsByName("editModalPlatforms[]");
    for(let i = 0; i < checkboxes.length; i++) {
        if(platforms.includes(checkboxes[i].value)) {
            checkboxes[i].checked = true;
        } else {
            checkboxes[i].checked = false;
        }
    }

    // Determine if there are any platforms that don't have predefined checkboxes
    document.getElementById("editModalCheckboxOther").checked = false;
    document.getElementById("editModalOtherPlatforms").value = "";
    let othersUsed = false;
    for(let i = 0; i < platforms.length; i++) {
        if(platformList.includes(platforms[i]) == false) {
            othersUsed = true;
            document.getElementById("editModalCheckboxOther").checked = true;
            document.getElementById("editModalOtherPlatforms").readOnly = false;
            document.getElementById("editModalOtherPlatforms").value += platforms[i] + ", ";
        }
    }

    // Remove trailing comma and space
    if(othersUsed) {
        document.getElementById("editModalOtherPlatforms").value = document.getElementById("editModalOtherPlatforms").value.slice(0, -2);
    }

    document.getElementById("editID").value = id;
    document.getElementById("editStatus").value = table;

    document.getElementById("modalbg").style.display = "block";
}

function changeTab(tabName) {
    let unreleasedTab = document.getElementById("unreleasedTab");
    let tbaTab = document.getElementById("tbaTab");
    let releasedTab = document.getElementById("releasedTab");
    let collectionTab = document.getElementById("collectionTab");
    let activeTab;

    unreleasedTab.classList.remove("activetab");
    tbaTab.classList.remove("activetab");
    releasedTab.classList.remove("activetab");
    collectionTab.classList.remove("activetab");

    if(tabName == "unreleased") {
        activeTab = unreleasedTab;
    } else if(tabName == "tba") {
        activeTab = tbaTab;
    } else if(tabName == "released") {
        activeTab = releasedTab;
    } else if(tabName == "collected") {
        activeTab = collectionTab;
    } else if(tabName == "collection") {
        activeTab = collectionTab;
        tabName = "collected";
    }
    
    activeTab.classList.add("activetab");

    if(activeTab.id == "unreleasedTab") {
        document.getElementById("unreleasedWrapper").style.display = "block";
        document.getElementById("tbaWrapper").style.display = "none";
        document.getElementById("releasedWrapper").style.display = "none";
        document.getElementById("collectionWrapper").style.display = "none";
    } else if(activeTab.id == "tbaTab") {
        document.getElementById("unreleasedWrapper").style.display = "none";
        document.getElementById("tbaWrapper").style.display = "block";
        document.getElementById("releasedWrapper").style.display = "none";
        document.getElementById("collectionWrapper").style.display = "none";
    } else if(activeTab.id == "releasedTab") {
        document.getElementById("unreleasedWrapper").style.display = "none";
        document.getElementById("tbaWrapper").style.display = "none";
        document.getElementById("releasedWrapper").style.display = "block";
        document.getElementById("collectionWrapper").style.display = "none";
    } else if(activeTab.id == "collectionTab") {
        document.getElementById("unreleasedWrapper").style.display = "none";
        document.getElementById("tbaWrapper").style.display = "none";
        document.getElementById("releasedWrapper").style.display = "none";
        document.getElementById("collectionWrapper").style.display = "block";
    }

    const url = new URL(window.location);
    url.searchParams.set("t", tabName);
    window.history.replaceState({}, "", url);
}

function deleteGame(gameID, table) {
    if(confirm("Are you sure you want to delete this game?\nThis cannot be undone!")) {
        window.location.replace(`./?delete=${gameID}&from=${table}`);
    }
}

let params = new URLSearchParams(window.location.search);
let switchTable = params.get("t");
if(switchTable) {
    changeTab(switchTable);
}

document.getElementById("customSearchEngineBox").value = searchEngineCustomURL;
searchEngineHandler();
applySearchEngine();
