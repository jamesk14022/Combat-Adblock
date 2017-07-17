// Function called if AdBlock is not detected
function adBlockNotDetected() {
	alert('AdBlock is not enabled');
}
// Function called if AdBlock is detected
function adBlockDetected() {
	alert('AdBlock is enabled');
}

// Recommended audit because AdBlock lock the file 'fuckadblock.js' 
// If the file is not called, the variable does not exist 'fuckAdBlock'
// This means that AdBlock is present
if(typeof fuckAdBlock === 'undefined') {
	adBlockDetected();
} else {
	fuckAdBlock.onDetected(adBlockDetected);
	fuckAdBlock.onNotDetected(adBlockNotDetected);
	// and|or
	fuckAdBlock.on(true, adBlockDetected);
	fuckAdBlock.on(false, adBlockNotDetected);
	// and|or
	fuckAdBlock.on(true, adBlockDetected).onNotDetected(adBlockNotDetected);
}

// Change the options
fuckAdBlock.setOption('checkOnLoad', false);
// and|or
fuckAdBlock.setOption({
	debug: true,
	checkOnLoad: false,
	resetOnEnd: false
});
