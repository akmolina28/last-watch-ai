// let Facade = require('facade.js');

// function draw(image_name, predictions, mask_name) {
//     let stage = new Facade(document.querySelector('#event-snapshot')),
//         image = new Facade.Image('/storage/' + image_name, {
//             x: stage.width() / 2,
//             y: stage.height() / 2,
//             height: 480,
//             width: 640,
//             anchor: 'center'
//         });

//     let mask = null;
//     if (typeof mask_name !== 'undefined') {
//         mask = new Facade.Image('/storage/masks/' + mask_name, {
//             x: stage.width() / 2,
//             y: stage.height() / 2,
//             height: 480,
//             width: 640,
//             anchor: 'center'
//         });
//     }

//     let rects = [];
//     predictions.forEach(prediction => {
//         rects.push(new Facade.Rect({
//             x: prediction.x_min,
//             y: prediction.y_min,
//             width: prediction.x_max - prediction.x_min,
//             height: prediction.y_max - prediction.y_min,
//             lineWidth: 4,
//             strokeStyle: 'red',
//             fillStyle: 'rgba(0, 0, 0, 0)'
//         }));
//     })

//     // stage.resizeForHDPI();

//     // stage.context.webkitImageSmoothingEnabled = false;

//     stage.draw(function () {

//         this.clear();

//         this.addToStage(image);

//         if (mask) this.addToStage(mask);

//         for (let i = 0; i < rects.length; i++) {
//             this.addToStage(rects[i]);
//         }
//     });
// }

// if (typeof predictions !== undefined) {
//     draw(file_name, predictions);

//     let elements = document.getElementsByClassName('prediction');

//     let renderPrediction = function() {
//         let selectedPredictions = [JSON.parse(this.getAttribute("data-prediction"))];
//         console.log(selectedPredictions);
//         draw(file_name, selectedPredictions);
//     };

//     for (let i = 0; i < elements.length; i++) {
//         elements[i].addEventListener('click', renderPrediction, false);
//     }
// }
