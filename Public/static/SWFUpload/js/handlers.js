function preLoad() {
	if (!this.support.loading) {
		alert("You need the Flash Player to use SWFUpload.");
		return false;
	} else if (!this.support.imageResize) {
		alert("You need Flash Player 10 to upload resized images.");
		return false;
	}
}
function loadFailed() {
	alert("Something went wrong while loading SWFUpload. If this were a real application we'd clean up and then give you an alternative");
}

function fileDialogComplete(numFilesSelected, numFilesQueued) {
	try {
		if (numFilesQueued > 0) {
			this.startResizedUpload(this.getFile(0).ID, this.customSettings.thumbnail_width, this.customSettings.thumbnail_height, SWFUpload.RESIZE_ENCODING.JPEG, this.customSettings.thumbnail_quality, false);
		}
	} catch (ex) {
		this.debug(ex);
	}
}

function uploadProgress(file, bytesLoaded) {

	try {
		var percent = Math.ceil((bytesLoaded / file.size) * 100);

		var progress = new FileProgress(file,  this.customSettings.upload_target);
		progress.setProgress(percent);
		progress.setStatus("Uploading...");
		progress.toggleCancel(true, this);
	} catch (ex) {
		this.debug(ex);
	}
}

// function uploadSuccess(file, serverData) {
// 	try {
// 		var progress = new FileProgress(file,  this.customSettings.upload_target);
//
// 		if (serverData.substring(0, 7) === "FILEID:") {
// 			changeImg("thumbnail.php?id=" + serverData.substring(7));
//
// 			progress.setStatus("Upload Complete.");
// 			progress.toggleCancel(false);
// 		} else {
// 			changeImg("images/error.gif");
// 			progress.setStatus("Error.");
// 			progress.toggleCancel(false);
// 			alert(serverData);
// 			// var url = JSON.parse(serverData).path;
// 			// $("#thumbnails img").attr("src" , url)
// 			changeImg(url)
// 		}
//
//
// 	} catch (ex) {
// 		this.debug(ex);
// 	}
// }

function uploadSuccess(file, serverData) {
	var url = JSON.parse(serverData).path;
		$("#thumbnails img").attr("src" , url)
	changeImg(url);
}
function uploadComplete(file) {
	try {
		/*  I want the next upload to continue automatically so I'll call startUpload here */
		if (this.getStats().files_queued > 0) {
			this.startResizedUpload(this.getFile(0).ID, this.customSettings.thumbnail_width, this.customSettings.thumbnail_height, SWFUpload.RESIZE_ENCODING.JPEG, this.customSettings.thumbnail_quality, false);
		} else {
			var progress = new FileProgress(file,  this.customSettings.upload_target);
			progress.setComplete();
			progress.setStatus("All images received.");
			progress.toggleCancel(false);
		}
	} catch (ex) {
		this.debug(ex);
	}
}

function uploadError(file, errorCode, message) {
	var imageName =  "error.gif";
	var progress;
	try {
		switch (errorCode) {
		case SWFUpload.UPLOAD_ERROR.FILE_CANCELLED:
			try {
				progress = new FileProgress(file,  this.customSettings.upload_target);
				progress.setCancelled();
				progress.setStatus("Cancelled");
				progress.toggleCancel(false);
			}
			catch (ex1) {
				this.debug(ex1);
			}
			break;
		case SWFUpload.UPLOAD_ERROR.UPLOAD_STOPPED:
			try {
				progress = new FileProgress(file,  this.customSettings.upload_target);
				progress.setCancelled();
				progress.setStatus("Stopped");
				progress.toggleCancel(true);
			}
			catch (ex2) {
				this.debug(ex2);
			}
		case SWFUpload.UPLOAD_ERROR.UPLOAD_LIMIT_EXCEEDED:
			imageName = "uploadlimit.gif";
			break;
		default:
			alert(message);
			break;
		}

		changeImg("images/" + imageName);

	} catch (ex3) {
		this.debug(ex3);
	}

}


// function addImage(src) {
// 	var newImg = document.createElement("img");
// 	newImg.style.margin = "5px";
// 	newImg.style.verticalAlign = "middle";
// 	var divThumbs = document.getElementById("thumbnails");
// 	divThumbs.insertBefore(newImg, divThumbs.firstChild);
// 	if (newImg.filters) {
// 		try {
// 			newImg.filters.item("DXImageTransform.Microsoft.Alpha").opacity = 0;
// 		} catch (e) {
// 			// If it is not set initially, the browser will throw an error.  This will set it if it is not set yet.
// 			newImg.style.filter = 'progid:DXImageTransform.Microsoft.Alpha(opacity=' + 0 + ')';
// 		}
// 	} else {
// 		newImg.style.opacity = 0;
// 	}
//
// 	newImg.onload = function () {
// 		fadeIn(newImg, 0);
// 	};
// 	newImg.src = src;
// }


function changeImg(src) {
	var img = document.getElementsByClassName("company-logo")[0];
	img.src = src;
}
function fadeIn(element, opacity) {
	var reduceOpacityBy = 5;
	var rate = 30;	// 15 fps
	if (opacity < 100) {
		opacity += reduceOpacityBy;
		if (opacity > 100) {
			opacity = 100;
		}
		if (element.filters) {
			try {
				element.filters.item("DXImageTransform.Microsoft.Alpha").opacity = opacity;
			} catch (e) {
				// If it is not set initially, the browser will throw an error.  This will set it if it is not set yet.
				element.style.filter = 'progid:DXImageTransform.Microsoft.Alpha(opacity=' + opacity + ')';
			}
		} else {
			element.style.opacity = opacity / 100;
		}
	}
	if (opacity < 100) {
		setTimeout(function () {
			fadeIn(element, opacity);
		}, rate);
	}
}
