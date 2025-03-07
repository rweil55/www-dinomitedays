// JavaScript Document

function showPicture(selectedValue) {
    console.log(selectedValue);
    if (selectedValue == "") {
        return;
    }
    var m = "<img src=\"/designs/images/" + selectedValue + "_sm.jpg\"  alt=\"image not available\" height=\"150px\"/>";
    var place = document.getElementById("pic1");
    place.innerHTML = m;
    
    // Import the filesystem module
    
    const contents = readSync(dir);
 
contents.forEach(path => console.log(`Matched path: "${path}"`));

    /*
const fs = require('fs');
// Function to get current filenames
// in directory
    var dir = "/home/pillowan/www-dinomitedays/designs/images/"';
    console.log ("red directory " . dir);
fs.readdir(__dirname, (err, files) => {
  if (err)
    console.log(err);
  else {
    console.log("\nCurrent directory filenames:");
    files.forEach(file => {
      console.log(file);
    })
  }
})

*/
    
}

function countChars(countfrom, displayto, limit) {
    // alert ( "cou - " + countfrom + ", disp - " + displayto + ",lim - "+ limit); 
    var len = document.getElementById(countfrom).value.length;
    var remaining = limit - len;
    document.getElementById(displayto).innerHTML = remaining;
    if (remaining < 0) {
        document.getElementById(displayto).style.color = "red";
    }
}

function submitClick(e) {
    console.log ("inot submit routie " + e);
    e.value = "procesing the file, this may take some time.";
    e.disable = true;
    return true;
}

function dropzoneDragOver(e) {
    e.addEventListener('start', (event) => {
        event.preventDefault();
    });
   e.addEventListener('drop', (event) => {
        event.preventDefault();
    });
      e.classList.add("drop-zone--over");
    console.log("into over");
}

function dropzoneDragLeave_end(e) {
    e.classList.remove("drop-zone--over");
    console.log("into leav");
}

function dropzone_chaange(e, inputName) {
     console.log("into change");
    return;
        e.addEventListener('change', (event) => {
        event.preventDefault(); 
    });
    if (e.files.length) {
        var inputElement = document.getElementById(inputName);
        updateThumbnail(e, inputElement.files[0]);
    }
}

function dropzone_click(inputName) {

    var inputElement = document.getElementById(inputName);
    inputElement.click();
}

function dropzone_drop(e) {
    console.log("into drop" + e );
    var inputName = e.target.id;
    inputName = inputName.replace("dropzone_", "");
    var inputElement = document.getElementById(inputName);
   //  if (e.dataTransfer.files.length) {
        var filedata =  e.dataTransfer.files
        console.log (filedata);
        console.log ("assigned to element" + inputElement.id)
        inputElement.files = filedata; 
        updateThumbnail(e.target, filedata[0]);
   // }
}
/*
document.querySelectorAll(".drop-zone__input").forEach((inputElement) => {
    const dropZoneElement = inputElement.closest(".drop-zone");

    dropZoneElement.addEventListener("click", (e) => {
        inputElement.click();
    });

    inputElement.addEventListener("change", (e) => {
        if (inputElement.files.length) {
            updateThumbnail(dropZoneElement, inputElement.files[0]);
        }
    });
      dropZoneElement.addEventListener("dragover", (e) => {  
        e.this();
        dropZoneElement.classList.add("drop-zone--over");
      });
   ["dragleave", "dragend"].forEach((type) => {
        dropZoneElement.addEventListener(type, (e) => {
            dropZoneElement.classList.remove("drop-zone--over");
        });
    });

    dropZoneElement.addEventListener("drop", (e) => {
        e.preventDefault();

        if (e.dataTransfer.files.length) {
            inputElement.files = e.dataTransfer.files;
            console.log("e." + e.dataTransfer.files);
            console.log("element" + inputElement.files)
            updateThumbnail(dropZoneElement, e.dataTransfer.files[0]);
        }

        dropZoneElement.classList.remove("drop-zone--over");
    });
});
     */

/**
 * Updates the thumbnail on a drop zone element.
 *
 * @param {HTMLElement} dropZoneElement
 * @param {File} file
 */
function updateThumbnail(dropZoneElement, file) {
    console.log("into update thumb");
    let thumbnailElement = dropZoneElement.querySelector(".drop-zone__thumb");

    // First time - remove the prompt
    if (dropZoneElement.querySelector(".drop-zone__prompt")) {
        dropZoneElement.querySelector(".drop-zone__prompt").remove();
    }

    // First time - there is no thumbnail element, so lets create it
    if (!thumbnailElement) {
        thumbnailElement = document.createElement("div");
        thumbnailElement.classList.add("drop-zone__thumb");
        dropZoneElement.appendChild(thumbnailElement);
    }

    thumbnailElement.dataset.label = file.name;

    // Show thumbnail for image files
    if (file.type.startsWith("image/")) {
        const reader = new FileReader();

        reader.readAsDataURL(file);
        reader.onload = () => {
            thumbnailElement.style.backgroundImage = `url('${reader.result}')`;
        };
    } else {
        thumbnailElement.style.backgroundImage = null;
    }
}
