(function () {
    'use strict';

    var boundary;
    var canvas;
    var nodes = [];
    var threshold;
    var numNodes = 100;
    var speed = 0.15;

    class Vector2D {
        constructor(x, y) {
            this.x = x;
            this.y = y;
        }
    }

    class Line {
        constructor(x1, y1, x2, y2) {
            this.x1 = x1;
            this.y1 = y1;
            this.x2 = x2;
            this.y2 = y2;
        }
    }

    class Node {
        constructor(x, y, radius, target) {
            this.radius = radius;
            this.x = x;
            this.y = y;
            this.location = new Vector2D(this.x, this.y);
            this.target = target;
            this.direction = normalize(new Vector2D(target.x - this.x, target.y - this.y));
        }

        move() {
            this.x += this.direction.x * speed;
            this.y += this.direction.y * speed;
        }

        show(context, alpha) {
            context.fillStyle = "rgba(76, 107, 34," + alpha + ")";
            context.beginPath();
            context.arc(this.x, this.y, this.radius, 0, 2 * Math.PI);
            context.fill();
        }
    }

    function getDistance(v1, v2) {
        return Math.sqrt(Math.pow(v2.x - v1.x, 2) + Math.pow(v2.y - v1.y, 2));
    }

    function getRandomPoint(line) {
        let u = Math.random();
        let x = ((1 - u) * line.x1 + u * line.x2);
        let y = ((1 - u) * line.y1 + u * line.y2);
        return new Vector2D(x, y);
    }

    function normalize(vector) {
        let magnitude = Math.sqrt(Math.pow(vector.x, 2) + Math.pow(vector.y, 2));
        return new Vector2D(vector.x / magnitude, vector.y / magnitude);
    }

    function drawLine(context, v1, v2, alpha) {
        context.strokeStyle = "rgba(76, 107, 34," + alpha + ")";
        context.beginPath();
        context.moveTo(v1.x, v1.y);
        context.lineTo(v2.x, v2.y);
        context.stroke();
    }

    function createNode() {
        let index = Math.floor(Math.random() * 4);
        let randLine = boundary[index];
        let startingPoint = new Vector2D(Math.random() * canvas.width, Math.random() * canvas.height);

        let randLine2 = randLine;
        while (randLine2 === randLine) {
            index = Math.floor(Math.random() * 4);
            randLine2 = boundary[index];
        }

        let targetPoint = getRandomPoint(randLine2);
        return new Node(startingPoint.x, startingPoint.y, 2, targetPoint);
    }

    function nearFieldNodes(canvas) {
        let context = canvas.getContext("2d");

        let nodesInBounds = [];
        for (let i = 0; i < nodes.length; i++) {
            if (nodes[i].x < 0 || nodes[i].x > canvas.width || nodes[i].y < 0 || nodes[i].y > canvas.height) {
                continue;
            }
            nodesInBounds.push(nodes[i]);
        }

        for (let i = 0; i < nodes.length; i++) {
            for (let j = 0; j < nodes.length; j++) {
                if (j === i) continue;

                let distance = getDistance(nodes[i], nodes[j]);

                if (distance < threshold) {
                    let alpha = 1 - (distance / threshold);
                    alpha = alpha.toFixed(1);
                    drawLine(context, nodes[i], nodes[j], alpha);
                }
            }
        }

        for (let i = 0; i < nodes.length; i++) {
            nodes[i].show(context, 1);
            nodes[i].move();
        }

        nodes = nodesInBounds;
        while (nodes.length < numNodes) {
            nodes.push(createNode());
        }
    }

    function init() {
        if (window.particlesAnimation) {
            cancelAnimationFrame(window.particlesAnimation);
            window.particlesAnimation = null;
        }

        nodes = [];
        canvas = document.getElementById('canvas');

        if (!canvas) return;

        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;

        let context = canvas.getContext("2d");
        context.clearRect(0, 0, canvas.width, canvas.height);

        boundary = [
            new Line(0, 0, canvas.width, 0),
            new Line(canvas.width, 0, canvas.width, canvas.height),
            new Line(canvas.width, canvas.height, 0, canvas.height),
            new Line(0, canvas.height, 0, 0),
        ];

        threshold = 100;

        for (let i = 0; i < numNodes; i++) {
            nodes.push(createNode());
        }

        window.particlesAnimation = requestAnimationFrame(draw);
    }

    function draw() {
        canvas = document.getElementById('canvas');
        if (!canvas) {
            return; // Stop animation if canvas is not found
        }
        let context = canvas.getContext("2d");

        context.globalCompositeOperation = 'destination-over';
        context.clearRect(0, 0, canvas.width, canvas.height);

        nearFieldNodes(canvas);

        window.particlesAnimation = requestAnimationFrame(draw);
    }

    // Initialize particles on page load
    window.addEventListener('load', () => {
        nodes = [];
        init();
    });

    // Also initialize on DOMContentLoaded for faster loading
    document.addEventListener('DOMContentLoaded', () => {
        nodes = [];
        init();
    });

    // Additional check for pages that might load the script dynamically
    // or when the canvas element becomes available
    function checkAndInitParticles() {
        const canvas = document.getElementById('canvas');
        if (canvas && !window.particlesAnimation) {
            nodes = [];
            init();
        }
    }

    // Check every 100ms for up to 5 seconds if particles haven't initialized
    let checkCount = 0;
    const maxChecks = 50;
    const checkInterval = setInterval(() => {
        checkAndInitParticles();
        checkCount++;
        if (checkCount >= maxChecks || window.particlesAnimation) {
            clearInterval(checkInterval);
        }
    }, 100);

})();
