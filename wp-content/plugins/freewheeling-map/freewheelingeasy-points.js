/*		Freewheeling Easy Mapping Application
 *
 *      A collection of routines for display of trail maps and amenities
 *
 *		copyright Roy R Weil 2019 - https://royweil.com
 *
 */
//	rweil routines to output paring icons google map
// called from call back of a xmllhttpRequest infreewheelingeasy_map.js
//
function _freewheelingmap_processMileposts() {
    var allDebug = false;
    var debugMilepost = allDebug;
    var debugMilePostData = allDebug;
    var debugiconurl = allDebug;
    var debugViewRange = allDebug;
    var debugListDisplayed = allDebug;
    var debugexclude = allDebug;
    if (xmlhttpLine.readyState !== 4)
        return; // do nothing until we have the complete files
    if (xmlhttpLine.status !== 200) { // got some sort of error .
        console.log("E#418 xmlhttpLine.status = " + xmlhttpLine.status
            + " - while processing the trail file");
        return false;
    }
    if (freewheeling_debug_fileload) {
        console.log("E#419 Start points");
    }
    fileString = xmlhttpLine.responseText;
    if (fileString.length < 50) {
        cpnsole.log("E#873 the input files is less than 59 charactere ");
        console.log(fileString);
    }
    var iiTitle1 = fileString.indexOf("<title") + 7;
    var iiTitle2 = fileString.indexOf("</title");
    var title = fileString.substring(iiTitle1, iiTitle2);
    var iifilename = title.lastIndexOf(".");
    var filename = title.substr(0, iifilename);
    console.log(" ------------- ");
    console.log(title);
    var iibody = fileString.indexOf("<body");
    iibody = fileString.indexOf(">", iibody);
    fileString = fileString.substr(iibody + 1);
    var data1 = fileString.split("|");
    if (debugMilepost) {
        console.log("data1.length " + data1.length);
        console.log(filename + ",  " + data1[0]);
    }
    var itemCnt = 0; // number of items per line in the file
    for (iifirst = 0; iifirst < 20; iifirst = iifirst + 1) {
        if (debugMilePostData) {
            console.log("x search " + iifirst + " - " + data1[iifirst]);
        }
        if (data1[iifirst] === "x") {
            itemCnt = iifirst;
            break;
        }
    }
    if (itemCnt === 0) {
        console.log("E R R O R  E#873 did not find an x in " + title);
        console.log(fileString.substr(1, 200));
        console.log(" ===================== ");
        itemCnt = 10;
    }
    if (debugMilePostData) {
        console.log("Milepostitemocnt is " + itemCnt);
        for (ii = 0; ii < 44; ii = ii + 1) {
            console.log(ii + " - " + data1[ii]);
        }
    }
    var minClusterZoom = 9;
    var maxClusterZoom = 100;
    var ShouldRangeBeChecked = false;
    var clusterurl = freewheeling_pagesPreBuiltUrl + "/icon-google/mile.png";
    var clusterZindexTop = 999; // undefined MAX_ZINDEX + 10;
    var clusterZindex = clusterZindexTop;
    var notClustered = true;
    console.log("process type = " + processType);
    switch (processType) {
        case "mile":
        case "mile10":
            minClusterZoom = 10;
            break;
        case "mile05":
            minClusterZoom = 11;
            break;
        case "mile01":
            minClusterZoom = 12;
            break;
        case "launch":
            clusterurl = freewheeling_pagesPreBuiltUrl + "/icon-google/flag.png";
            minClusterZoom = 13;
            clusterZindex = clusterZindexTop - 1
            break;
        case "park":
            clusterurl = freewheeling_pagesPreBuiltUrl + "/icon-google/park.png";
            minClusterZoom = 12;
            clusterZindex = clusterZindexTop - 3
            notClustered = false
            break;
        case "boat":
            clusterurl = freewheeling_pagesPreBuiltUrl + "/icon-google/camp.png";
            minClusterZoom = 12;
            clusterZindex = clusterZindexTop - 3
            break;
        case "camp":
            clusterurl = freewheeling_pagesPreBuiltUrl + "/icon-google/camp.png";
            minClusterZoom = 12;
            clusterZindex = clusterZindexTop - 3
            break;
        case "dino":
            clusterurl = freewheeling_pagesPreBuiltUrl + "/icon-google/dino.png";
            minClusterZoom = 11;
            ShouldRangeBeChecked = true;
            clusterZindex = clusterZindexTop - 4
            notClustered = false
            break;
        case "amen":
        case "location":
            if (debugexclude) console.log("exclude" + exclude);
            minClusterZoom = 11;
            ShouldRangeBeChecked = true;
            clusterZindex = clusterZindexTop - 4
            notClustered = false
            break;
        default:
            logErr("E#420 unkown type " + processType);
            break;
    } // end switch for adding markers 
    var cntInfo = 0;
    var cntSkipName = 0;
    var cntSkipDuplatlng = 0;
    var cntSkipDuplicate = 0;
    var cntSkipLatZero = 0;
    var cntSkipLaunch = 0;
    var cntSkipNoMiles = 0;
    var cntSkipRange = 0;
    var htmlInfo = "";
    var markers_milepost = [];
    var milepostLngShift;
    if (title.indexOf("amen.txt")) {
        milepostLngShift = .00005;
    } else {
        milepostLngShift = 0;
    }
    // init to test for points outside the window 
    mapBounds = mapScreen.getBounds();
    if ('undefined' == typeof mapBounds) {
        var latMaxLimit = 90;
        var latMinLimit = -90;;
        var lngMaxLimit = 90;
        var lngMinLimit = -90;
    } else {
        var latMaxLimit = mapBounds.getNorthEast().lat() + 0.3;
        var latMinLimit = mapBounds.getSouthWest().lat() - 0.3;
        var lngMaxLimit = mapBounds.getNorthEast().lng() + 0.3;
        var lngMinLimit = mapBounds.getSouthWest().lng() - 0.3;
    }
    // save last point, to aviod duplicate  - assumes point are i lat,lng order
    var latPast = 200;
    var lngPast = 200;
    if (debugViewRange) {
        console.log("northlimit is " + latMaxLimit + ", min " + latMinLimit);
        console.log("eastWestlimit is " + lngMaxLimit + ", min " + lngMinLimit);
    }
    var ii;
    var imageExtension = "png";
    var iconBlankURL = freewheeling_pagesPreBuiltUrl + "/icon-milepost/imgBlank.jpg";
    var iconBlankLightGreenURL = freewheeling_pagesPreBuiltUrl
        + "/icon-milepost/imgBlankLightGreenURL.jpg";
    var iconBlankLightHotPinkURL = freewheeling_pagesPreBuiltUrl
        + "/icon-milepost/imgBlankLightHotPinkURL.jpg";
    + "/icon-milepost/imgBlankLightHotPinkURL.jpg";
    var iconParkURL = freewheeling_pagesPreBuiltUrl + "/icon-amenities/park." + imageExtension;
    var iconCampURL = freewheeling_pagesPreBuiltUrl + "/icon-amenities/Camping.png";
    var iconDinoURL = freewheeling_pagesPreBuiltUrl + "/icon-google/dino.png";
    var iconDinoredURL = freewheeling_pagesPreBuiltUrl + "/icon-google/dinored.png";
    console.log("bizNameRequest " + freewheeling_bizNameRequest);
    if (debugiconurl) {
        console.log("processType " + processType);
        console.log("iconBlankURL " + iconBlankURL);
        console.log("iconBlankLightGreenURL " + iconBlankLightGreenURL);
        console.log("iconBlankLightHotPinkURL " + iconBlankLightHotPinkURL);
        console.log("iconParkURL " + iconParkURL);
    }
    nextMarker: for (ii = itemCnt + 1; ii < data1.length; ii = ii + itemCnt) {
        //			console.log("ii is " + ii + "-- " + data1[ii + 4]);
        if (data1[ii].indexOf("body></html") !== -1) {
            break;
        }
        cntInfo++;
        var trailid = data1[ii + 0];
        trailid = trailid.substr(1);
        var lat = data1[ii + 1];
        var lng = data1[ii + 2];
        var bizName = data1[ii + 3];
        var iconstyle = data1[ii + 4];
        if (iconstyle === undefined) {
            console.log("E#863 iconstyle === undefined " + bizName);
        }
        var description = data1[ii + 5];
        if (debugMilePostData) {
            console.log(ii + " latlng " + lat + ", " + lng);
        }
        if (lat == "" || lng == "") {
            cntSkipLatZero++;
            continue; // reject missing latitude, longitude
        }
        if (isNaN(lat) || isNaN(lng)) {
            console.log("The latitude " + lat + "or longitude " + lng
                + " is not a number. now what? ");
            for (kk = ii; kk < ii + 20; kk = kk + 1) {
                console.log(data1[kk]);
            }
            break;
        }
        if (false) {
            console.log(cntInfo + " _ " + description + " -" + lat + ">" + latMaxLimit + "," + lat + "<"
                + latMinLimit + "," + lng + ">" + lngMaxLimit + "," + lng + "<" + lngMinLimit);
        }
        if (ShouldRangeBeChecked && (lat > latMaxLimit || lat < latMinLimit || lng > lngMaxLimit || lng < lngMinLimit)) {
            cntSkipRange++;
            continue; // reject not in view
        }
        if (lat == latPast && lng == lngPast) {
            cntSkipDuplatlng++;
            continue; // reject already displayed something here
        }
        latPast = lat;
        lngPast = lng;
        var milepostMilage = data1[ii + 6];
        var trail = data1[ii + 7];
        var istrailhead = data1[ii + 8];
        var pluscode = data1[ii + 9];
        if (milepostMilage == "99.000") {
            debugiconurl = true;
        } else {
            debugiconurl = false;
        }
        // --------------------------------------------- Got data, should re reject
        //	console.log(lat + "," + lng + "," + milepostMilage + "," + bizid);
        if (debugListDisplayed) console.log("display " + bizName);
        var markerOptions;
        var myLatlng = new google.maps.LatLng(lat, lng); // + milepostLngShift);
        var dragState = true;
        switch (processType) {
            case "launch":
                if (milepostMilage.length < 1) {
                    cntSkipNoMiles++
                    continue;
                }
                switch (iconstyle) {
                    case "mile-lightgreen":
                        icon = iconBlankLightGreenURL;
                        break;
                    case "mle-lightred":
                        icon = iconBlankLightHotPinkURL;
                        break;
                    default:
                        icon = iconBlankURL
                }
                markerOptions = {
                    //                map: mapScreen,
                    icon: icon,
                    label: milepostMilage,
                    title: bizName + "-" + trail + " - " + description
                };
                break;
            case "dino":
                switch (iconstyle) {
                    case "dinored.png":
                        iconurl = iconDinoredURL;
                        break;
                    case "dino.png":
                        iconurl = iconDinoURL;
                        break;
                    default:
                        console.log("Unknown icon style of " + iconstyle);
                        break;
                } // end switch
                markerOptions = {
                    //                map: mapScreen,
                    icon: iconurl,
                    title: bizName,
                    label: "",
                    trailid: trailid,
                    clickUrl: "https://dinomitedays.org/designs/" + trailid + ".htm'",
                }; // end make opitions
                break;
                //                 infoWindow: {
                //                     content: "<img src='https://dinomitedays.org/designs/images/"
                //                       + trailid + "_sm.jpg' />"
                //             }            
            case "mile10":
            case "mile05":
            case "mile01":
                var fileicon = freewheeling_pagesPreBuiltUrl + "/icon-milepost/" + iconstyle;
                console.log("map iconstyle " + iconstyle);
                console.log("map freewheeling_pagesPreBuiltUrl " + freewheeling_pagesPreBuiltUrl);
                console.log("map icon " + fileicon);
                markerOptions = {
                    //                map: mapScreen,
                    position: myLatlng,
                    icon: fileicon,
                    title: bizName + "-" + trail + " - " + description
                };
                break;
            case "park":
                markerOptions = {
                    //                map: mapScreen,
                    icon: iconParkURL,
                    title: bizName + " near MP " + milepostMilage + " on " + trail + " - " + description,
                    label: ""
                };
                break;
            case "camp":
                if (cntInfo < 5) {
                    //   logLine( myLatlng  + bizName);
                }
                markerOptions = {
                    //                map: mapScreen,
                    icon: iconCampURL,
                    title: bizName + " near MP " + milepostMilage + " on " + trail + " - " + description,
                    label: ""
                };
                break;
            case "trailcenter":
                markerOptions = {
                    //                map: mapScreen,
                    icon: iconParkURL,
                    title: " MP " + milepostMilage + " on " + trail + " - " + description,
                    label: description
                };
                break;
            case "amen":
            case "location":
                if (freewheeling_bizNameRequest != '') {
                    if (bizName.indexOf(freewheeling_bizNameRequest) < 0) {
                        cntSkipName++;
                        continue; // reject - only what one business
                    }
                    console.log("displaying icon" + iconstyle);
                }
                switch (iconstyle) { // this is looking a 
                    case "park":
                        iconstyle = "park.png"
                        break;
                    case "launch":
                        cntSkipLaunch++;
                        continue; // ignore it
                    default:
                        // do nothing - the assoiated icon should be visable
                        break;
                }
                iconstyle = iconstyle.replace("jpg", imageExtension)
                // code here to use exclude
                for (iiex = 0; iiex < exclude.length; iiex++) {
                }
                var iconurl = freewheeling_pagesPreBuiltUrl + "/icon-amenities/" + iconstyle;
                if (iconurl.includes("undef")) {
                    console.log("E#864 " + bizName + " - " + iconurl);
                }
                markerOptions = {
                    //                map: mapScreen,
                    icon: iconurl,
                    title: "near MP " + milepostMilage + " on " + trail + " - " + description,
                    clickUrl: "https:///freewheelingeasy-amenity/?amenity=" + bizName,
                    label: ""
                };
                break;
            case "line":
                logErr("E#859 E R R O R into mileposts with a line ");
                return;
            default:
                logErr("E#859 E R R O R into mileposts with unknown type " + processType);
                return;
        } // end switch
        // common maker options
        markerOptions["draggable"] = true;
        markerOptions["map"] = mapScreen;
        markerOptions["position"] = myLatlng;
        markerOptions["visible"] = false;
        markerOptions["zIndex"] = clusterZindex;
        for (jj = 0; jj < markers_milepost.length; jj = jj + 1) {
            if (markers_milepost[jj].positions == myLatlng) {
                console.log("duplicate" + bizname + " - " + markers_milepost.title);
                cntSkipDuplicate++;
                continue;
            }
        }
        var marker = new google.maps.Marker(markerOptions);
        if (debugiconurl) {
            console.log("markers_milepost.length" + markers_milepost.length);
            console.log("marker" + markerOptions)
        }
        /*
                google.maps.event.addListener(marker, 'mouseover', function (ev) {
                    if (debugLineHover) {
                        console.log("into the polyThing mouseover");
                        console.dir("event data " + JSON.stringify(ev));
                        console.log("lat" + ev.latLng.lat);
                    }
                    infoWindowsOpen.forEach(closeinfo);
                    var ParkingInfoWindow = new google.maps.InfoWindow({
                        content: "loading...",
                        zIndex: 50,
                        PixelOffset: (10, 10)
                    });
                    ParkingInfoWindow.setPosition(ev.latLng);
                    ParkingInfoWindow.setContent(this.title);
                    ParkingInfoWindow.open(mapScreen, this);
                    infoWindowsOpen.push(ParkingInfoWindow); // save it away for close
                    setTimeout('infoWindowsOpen.forEach (closeinfo)', '4500');
                    if (debugLineHover) {
                        console.log("exit the polyThing hover");
                    }
                });
        */
        
        console.log ("process type" + processType);
        switch (processType) { // handle cliable markers that launch a page
  
            case "dino":
                var allow = document.getElementById("allowdrag");
                console.log("allow.value is " + allow.value);
                if (allow.value != "allow") { 
                    break;  
                }      
                marker.addListener('mouseover', function () {        
                    var info = new google.maps.InfoWindow({
                        content: "<span style='font-weight:bold; font-size:large;' >" + this.title
                            + "</span> [ <a href='https://dinomitedays.org/designs/"
                            + this.trailid + ".htm' > details</a> ] [ "
                            + " <img src='https://www.gstatic.com/images/icons/material/system/2x/directions_white_18dp.png'"
                            + "/> ]  <br><img src='https://dinomitedays.org/designs/images/"
                            + this.trailid + "_sm.jpg' height='250' />",
                        position: this.positon
                    });
                    info.open(mapScreen, this);
                });
                marker.addListener('dragend', function (a) {
                    var latitude = a.latLng.lat().toFixed(7);
                    var longitude = a.latLng.lng().toFixed(7);
                    var savetrailid = "";
                    var allow = document.getElementById("allowdrag");
                    console.log("allow.value is " + allow.value);
                    if (allow.value == "allow") { 
                        console.log (a) 
                        var parr = a.domEvent.target;
              //          console.log (parr);
                        var trailid = parr.trailid;
                        var url = "https://edit.shaw-weil/com/update-database" +
                            "?table=pillowan_wp451.wpprrj_00rrwdinos" +
                            "&keyfiled=filename&keyvalue=" + this.trailid +
                            "&field=latitue&newvalue=" + latitude ;
                        
                        alert(url);
                    } else {
                        alert ("relocation of dinasaurs not enabled. Click the button in lower left");
                    }
                });
                break;
            case "aminity":
                marker.addListener('click', function () {
                    var url = this.clickUrl;
                    url = url.replace(" ", "+");
                    window.open(url, width = 400, height = 200);
                });
                break;
            default:
                // only the above icons are clickable.
        } // end switch (processid)
        markers_milepost.push(marker);
    } // end for (ii = itemCnt + 1; ii < data1.length; ii = ii + itemCnt) {
    // assert markerist is built as a collection of markers
    if (notClustered) {
        item = new freewheeling_marker_group(processType, markers_milepost, minClusterZoom, maxClusterZoom);
    } else {
        markerCluster = new MarkerClusterer(mapScreen, markers_milepost);
        markerCluster.ignoreHidden_ = true;
        markerCluster.averageCenter_ = true;
        markerCluster.gridSize_ = 30;
        var white = "#ffffff";
        var red = "#ff0000";
        var icon = {
            url: clusterurl,
            height: 25,
            width: 25,
            textSize: 17,
            textColor: red,
            fontweight: "bold"
        };
        var anchor = new google.maps.Point(-25, -25);
        markerCluster.styles_[0] = icon;
        markerCluster.styles_[1] = icon;
        markerCluster.styles_[2] = icon;
        markerCluster.styles_[3] = icon;
        markerCluster.styles_[4] = icon;
        logLine(markerCluster);
        item = new freewweeling_cluster_group(processType, markerCluster, minClusterZoom,
            maxClusterZoom);
        if (false) {
            var ii = 0;
            //      logLine(markergroups[ii]);
            logLine(markergroups[ii].maxZoom);
            //    logLine(markergroups[ii].makerlist);
            logLine(markergroups[ii].makerlist.length);
        }
    } //  end  if (notClustered)
    markergroups.push(item);
    processZoom();
    cntDisplay = cntInfo - cntSkipLatZero - cntSkipName - cntSkipRange - cntSkipDuplicate
        - cntSkipDuplatlng - cntSkipLaunch - cntSkipNoMiles;
    console.log(processType + " read " + cntInfo
        + "\n Name skipped " + cntSkipName
        + "\n ranged skiped " + cntSkipRange
        + "\n duplicat eskikpped " + cntSkipDuplicate
        + "\n lat/lng duplicate Skipped " + cntSkipDuplatlng
        + "\n lat.lng zero Skippep " + cntSkipLatZero
        + "\n launch skipped " + cntSkipLaunch
        + "\n No miles skipped " + cntSkipNoMiles
        + "\n displayed " + cntDisplay
        + "\n markers_milepost.length " + markers_milepost.length);
    _freewheelingmap_readNextFileandOutput(); // get the next file, or display
}
//
function ParkingInfoWindowClick() {
    logErr("E#423");
    return true;
}
