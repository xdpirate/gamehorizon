let optionsBox = document.createElement("div");

optionsBox.innerHTML = `
    <b>Theme:</b><br />
    <input type="radio" name="themeRadio" onchange="applyTheme('daylight');saveSettings();" id="themeDaylight"${currentTheme == "daylight" ? " checked" : ""}><label for="themeDaylight">Daylight</label><br />
    <input type="radio" name="themeRadio" onchange="applyTheme('midnight');saveSettings();" id="themeMidnight"${currentTheme == "midnight" ? " checked" : ""}><label for="themeMidnight">Midnight</label><br />
    <input type="radio" name="themeRadio" onchange="applyTheme('nord');saveSettings();" id="themeNord"${currentTheme == "nord" ? " checked" : ""}><label for="themeNord">Nord</label>
    
    <hr>
        <label for="searchEngine">Search engine:</label>

        <select name="searchEngine" id="searchEngine" onchange="searchEngineHandler();saveSettings();">
            <option value="startpage"${searchEngine == "startpage" ? " selected" : ""}>Startpage</option>
            <option value="ddg"${searchEngine == "ddg" ? " selected" : ""}>DuckDuckGo</option>
            <option value="google"${searchEngine == "google" ? " selected" : ""}>Google</option>
            <option value="bing"${searchEngine == "bing" ? " selected" : ""}>Bing</option>
            <option value="yandex"${searchEngine == "yandex" ? " selected" : ""}>Yandex</option>
            <option disabled>----------</option>
            <option value="custom"${searchEngine == "custom" ? " selected" : ""}>Custom</option>
        </select><br />
        <input type="text" id="customSearchEngineBox" name="customSearchEngineBox" onkeyup="saveSettings();" placeholder="URL - use %s in place of search terms"></input>
    <hr>

    <input type="checkbox" onchange="saveSettings();" id="hideAddGameCheckbox"${hideAddGame == 1 ? " checked" : ""}><label for="hideAddGameCheckbox">Hide the Add Game area by default</label>
    
    <hr>

    <div>
        <b>Export data:</b>
        
        <form>
            <label for="export">Table to export:</label>

            <select name="export" id="export">
                <option value="all">All</option>
                <option value="unreleased">Unreleased</option>
                <option value="tba">Announced</option>
                <option value="released">Released</option>
                <option value="collected">Collected</option>
            </select>
            
            <br />
            
            Format: 
            <input type="radio" name="format" id="formatText" value="text" checked><label for="formatText">Plain text</label> <span title="Plain text exports only include game titles and platforms, no database IDs or release dates." onclick="javascript:alert('Plain text exports only include game titles and platforms, no database IDs or release dates.');">🛈</span>
            <input type="radio" name="format" id="formatCSV" value="csv"><label for="formatCSV">CSV</label>
            <input type="radio" name="format" id="formatJSON" value="json"><label for="formatJSON">JSON</label>
            <input type="radio" name="format" id="formatHTML" value="html"><label for="formatHTML">HTML</label>

            <br />

            Ordering: <input type="radio" name="order" id="orderAZ" value="az" checked><label for="orderAZ">Alphabetical</label>
            <input type="radio" name="order" id="orderDB" value="db"><label for="orderDB">Database</label>
            
            <br /><br />

            <input type="submit" value="Export">
        </form>
    </div>
`;

document.getElementById("optionsWrapper").appendChild(optionsBox);
