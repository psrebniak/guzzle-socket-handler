var express = require('express');
var bodyParser = require('body-parser');

const app = express();

app.use(bodyParser());

const config = {
    socket: '/tmp/socket.sock'
};

app.all('/*', (request, response) => {
    console.log(request.url);
    console.dir(request.query);
    console.dir(request.body);
    console.log();

    response.end('Hello World');
});


app.listen(config.socket, () => {
    console.info(`Listening on ${config.socket}`);
});

