var express = require('express');
var bodyParser = require('body-parser');
var multer  = require('multer');
const upload = multer({ dest: 'uploads/' });
const app = express();


app.use(bodyParser());

const config = {
    socket: '/tmp/socket.sock'
};

app.all('/*', upload.array(), (request, response) => {
    console.log(request.url);
    console.dir(request.query);
    console.dir(request.body);
    console.dir(request.files);
    console.log();

    response.end('Hello World');
    console.log('end');
});


app.listen(config.socket, () => {
    console.info(`Listening on ${config.socket}`);
});

