<?php
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>3D Kaasplank — Maarten</title>
    <style>
        :root {
            --bg-top: #261914;
            --bg-bottom: #110b08;
            --panel-bg: rgba(28, 19, 15, 0.78);
            --panel-border: rgba(245, 198, 118, 0.24);
            --accent: #f4b942;
            --accent-hover: #ffd06b;
            --text-main: #fcf5eb;
            --text-muted: #cbb8a3;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            user-select: none;
            -webkit-user-select: none;
        }

        html, body {
            width: 100%;
            height: 100%;
            overflow: hidden;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: radial-gradient(circle at 50% 42%, #3a261d 0%, var(--bg-top) 48%, var(--bg-bottom) 100%);
            color: var(--text-main);
        }

        #canvas-container {
            position: fixed;
            inset: 0;
            cursor: grab;
        }

        #canvas-container:active {
            cursor: grabbing;
        }

        /* Top-left info card */
        .hud-header {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 10;
            background: var(--panel-bg);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border: 1px solid var(--panel-border);
            border-radius: 16px;
            padding: 14px 18px;
            max-width: 340px;
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.42);
            pointer-events: auto;
        }

        .hud-title {
            font-size: 1.12rem;
            font-weight: 700;
            letter-spacing: -0.01em;
            display: flex;
            align-items: center;
            gap: 8px;
            color: #fff7e8;
        }

        .hud-subtitle {
            margin-top: 5px;
            font-size: 0.8rem;
            line-height: 1.4;
            color: var(--text-muted);
        }

        .status-row {
            margin-top: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.76rem;
            font-weight: 600;
            color: #f7dfb9;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #4ade80;
            box-shadow: 0 0 8px #4ade80;
            transition: background 0.25s ease, box-shadow 0.25s ease;
        }

        .status-dot.manual {
            background: #fbbf24;
            box-shadow: 0 0 8px #fbbf24;
        }

        .status-dot.paused {
            background: #94a3b8;
            box-shadow: none;
        }

        /* Toast notification when mouse peeks */
        .mouse-toast {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%) translateY(-16px);
            z-index: 12;
            background: rgba(38, 25, 18, 0.88);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(244, 185, 66, 0.45);
            border-radius: 999px;
            padding: 9px 18px;
            font-size: 0.86rem;
            font-weight: 600;
            color: #fff4dc;
            box-shadow: 0 10px 28px rgba(0, 0, 0, 0.45);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease, transform 0.3s cubic-bezier(0.22, 1, 0.36, 1);
            white-space: nowrap;
        }

        .mouse-toast.visible {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }

        /* Hover label on cheeses */
        .cheese-tooltip {
            position: fixed;
            z-index: 15;
            pointer-events: none;
            background: rgba(22, 14, 10, 0.88);
            border: 1px solid rgba(244, 185, 66, 0.4);
            color: #fff6e5;
            padding: 6px 11px;
            border-radius: 9px;
            font-size: 0.76rem;
            font-weight: 600;
            transform: translate(-50%, -135%);
            opacity: 0;
            transition: opacity 0.15s ease;
            white-space: nowrap;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.4);
        }

        .cheese-tooltip.visible {
            opacity: 1;
        }

        .cheese-tooltip small {
            display: block;
            font-weight: 400;
            color: var(--text-muted);
            font-size: 0.69rem;
        }

        /* Bottom control bar */
        .hud-controls {
            position: fixed;
            bottom: 22px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10;
            background: var(--panel-bg);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border: 1px solid var(--panel-border);
            border-radius: 20px;
            padding: 12px 18px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            gap: 14px;
            box-shadow: 0 14px 36px rgba(0, 0, 0, 0.5);
            max-width: calc(100vw - 24px);
        }

        .control-group {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .divider {
            width: 1px;
            height: 26px;
            background: rgba(255, 235, 205, 0.16);
        }

        .btn {
            appearance: none;
            border: 1px solid rgba(245, 198, 118, 0.25);
            background: rgba(255, 244, 224, 0.07);
            color: var(--text-main);
            font-size: 0.8rem;
            font-weight: 600;
            padding: 7px 12px;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.18s ease;
            white-space: nowrap;
        }

        .btn:hover {
            background: rgba(244, 185, 66, 0.2);
            border-color: rgba(244, 185, 66, 0.55);
        }

        .btn.active {
            background: var(--accent);
            border-color: var(--accent);
            color: #23150c;
            box-shadow: 0 2px 12px rgba(244, 185, 66, 0.35);
        }

        .btn-mouse {
            background: rgba(244, 185, 66, 0.18);
            border-color: rgba(244, 185, 66, 0.5);
            color: #ffe29e;
        }

        .btn-mouse:hover {
            background: var(--accent);
            color: #23150c;
        }

        .slider-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.76rem;
            color: var(--text-muted);
        }

        .slider-wrap input[type="range"] {
            width: 115px;
            accent-color: var(--accent);
            cursor: pointer;
        }

        .angle-readout {
            min-width: 32px;
            text-align: right;
            font-variant-numeric: tabular-nums;
            color: #ffe29e;
            font-weight: 600;
        }

        .fallback-error {
            position: fixed;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 24px;
            text-align: center;
            background: #1a110d;
            color: #fff4e0;
            z-index: 50;
        }

        @media (max-width: 780px) {
            .hud-header {
                top: 12px;
                left: 12px;
                right: 12px;
                max-width: none;
                padding: 10px 14px;
            }
            .divider {
                display: none;
            }
            .hud-controls {
                bottom: 12px;
                padding: 10px 12px;
                gap: 8px;
            }
        }
    </style>

    <script type="importmap">
    {
        "imports": {
            "three": "https://cdn.jsdelivr.net/npm/three@0.170.0/build/three.module.js",
            "three/addons/": "https://cdn.jsdelivr.net/npm/three@0.170.0/examples/jsm/"
        }
    }
    </script>
</head>
<body>
    <div id="canvas-container"></div>

    <div class="hud-header">
        <div class="hud-title">🧀 Bourgondische Kaasplank</div>
        <p class="hud-subtitle">
            Sleep om vrij te draaien en van perspectief te wisselen, scroll om te zoomen, of klik op een kaasje om het muisje te lokken.
        </p>
        <div class="status-row">
            <span id="status-dot" class="status-dot"></span>
            <span id="status-text">Draait automatisch (idle)</span>
        </div>
    </div>

    <div id="mouse-toast" class="mouse-toast">🐭 Piep!</div>
    <div id="cheese-tooltip" class="cheese-tooltip"></div>

    <div class="hud-controls">
        <div class="control-group" role="group" aria-label="Perspectief kiezen">
            <button type="button" class="btn" data-view="front">Vooraanzicht</button>
            <button type="button" class="btn active" data-view="angled">Schuin</button>
            <button type="button" class="btn" data-view="top">Bovenaanzicht</button>
        </div>

        <div class="divider"></div>

        <label class="slider-wrap" title="Schuif traploos tussen vooraanzicht en bovenaanzicht">
            <span>Voor</span>
            <input id="angle-slider" type="range" min="0" max="100" value="46">
            <span>Boven</span>
            <span id="angle-readout" class="angle-readout">41°</span>
        </label>

        <div class="divider"></div>

        <div class="control-group">
            <button type="button" id="toggle-idle-btn" class="btn active">⟳ Idle draaien: Aan</button>
            <button type="button" id="summon-mouse-btn" class="btn btn-mouse">🐭 Laat muisje piepen</button>
        </div>
    </div>

    <div id="fallback-error" class="fallback-error">
        <div>
            <h2>Kon Three.js niet laden</h2>
            <p style="margin-top:8px;color:#cbb8a3;">Controleer je internetverbinding of WebGL-ondersteuning.</p>
        </div>
    </div>

    <script type="module">
        import * as THREE from 'three';
        import { OrbitControls } from 'three/addons/controls/OrbitControls.js';

        const container = document.getElementById('canvas-container');
        const statusDot = document.getElementById('status-dot');
        const statusText = document.getElementById('status-text');
        const mouseToast = document.getElementById('mouse-toast');
        const cheeseTooltip = document.getElementById('cheese-tooltip');
        const angleSlider = document.getElementById('angle-slider');
        const angleReadout = document.getElementById('angle-readout');
        const toggleIdleBtn = document.getElementById('toggle-idle-btn');
        const summonMouseBtn = document.getElementById('summon-mouse-btn');
        const viewButtons = Array.from(document.querySelectorAll('[data-view]'));

        // Polar angle limits:
        // POLAR_TOP (~3 deg from +Y) = recht van boven (Bovenaanzicht)
        // POLAR_FRONT (~86 deg from +Y) = recht van voren op ooghoogte (Vooraanzicht)
        const POLAR_TOP = 0.05;
        const POLAR_FRONT = Math.PI * 0.478;
        const POLAR_ANGLED = 0.86;

        // --- Scene, Camera, Renderer ---
        const scene = new THREE.Scene();
        scene.fog = new THREE.FogExp2(0x1b110d, 0.022);

        const camera = new THREE.PerspectiveCamera(40, window.innerWidth / window.innerHeight, 0.1, 100);
        camera.position.set(0, 7.2, 8.6);

        const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true, powerPreference: 'high-performance' });
        renderer.setSize(window.innerWidth, window.innerHeight);
        renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
        renderer.shadowMap.enabled = true;
        renderer.shadowMap.type = THREE.PCFSoftShadowMap;
        renderer.toneMapping = THREE.ACESFilmicToneMapping;
        renderer.toneMappingExposure = 1.12;
        renderer.localClippingEnabled = true;
        container.appendChild(renderer.domElement);

        // --- OrbitControls ---
        const controls = new OrbitControls(camera, renderer.domElement);
        controls.target.set(0, 0.42, 0);
        controls.enableDamping = true;
        controls.dampingFactor = 0.065;
        controls.minPolarAngle = POLAR_TOP;
        controls.maxPolarAngle = POLAR_FRONT;
        controls.minDistance = 5.2;
        controls.maxDistance = 17.5;
        controls.enablePan = false;

        // --- Warm Bistro Lighting ---
        const hemiLight = new THREE.HemisphereLight(0xffe9c4, 0x3d2518, 0.95);
        scene.add(hemiLight);

        const keyLight = new THREE.DirectionalLight(0xfff1d6, 2.2);
        keyLight.position.set(6.5, 11.5, 7.5);
        keyLight.castShadow = true;
        keyLight.shadow.mapSize.set(2048, 2048);
        keyLight.shadow.camera.near = 2;
        keyLight.shadow.camera.far = 28;
        const d = 6.6;
        keyLight.shadow.camera.left = -d;
        keyLight.shadow.camera.right = d;
        keyLight.shadow.camera.top = d;
        keyLight.shadow.camera.bottom = -d;
        keyLight.shadow.bias = -0.0006;
        scene.add(keyLight);

        const rimLight = new THREE.DirectionalLight(0xffb86c, 0.9);
        rimLight.position.set(-7.5, 5.5, -6.5);
        scene.add(rimLight);

        const frontFill = new THREE.PointLight(0xffdfa8, 0.65, 22);
        frontFill.position.set(-3.5, 3.0, 7.5);
        scene.add(frontFill);

        // --- Procedural Canvas Textures ---
        function createWoodTextures() {
            const canvas = document.createElement('canvas');
            canvas.width = 1024;
            canvas.height = 1024;
            const ctx = canvas.getContext('2d');

            const bumpCanvas = document.createElement('canvas');
            bumpCanvas.width = 1024;
            bumpCanvas.height = 1024;
            const bctx = bumpCanvas.getContext('2d');

            ctx.fillStyle = '#9a6234';
            ctx.fillRect(0, 0, 1024, 1024);
            bctx.fillStyle = '#808080';
            bctx.fillRect(0, 0, 1024, 1024);

            // Plank seams & tonal variation
            const plankCount = 5;
            const plankH = 1024 / plankCount;
            for (let p = 0; p < plankCount; p++) {
                const y0 = p * plankH;
                const shade = (p % 2 === 0 ? 1 : -1) * 9;
                ctx.fillStyle = `rgb(${154 + shade}, ${98 + Math.round(shade * 0.7)}, ${52 + Math.round(shade * 0.4)})`;
                ctx.fillRect(0, y0, 1024, plankH);

                // Wood grain fibers
                for (let i = 0; i < 150; i++) {
                    const gy = y0 + Math.random() * plankH;
                    const alpha = 0.04 + Math.random() * 0.1;
                    const dark = Math.random() > 0.35;
                    ctx.strokeStyle = dark ? `rgba(58, 30, 12, ${alpha})` : `rgba(218, 162, 104, ${alpha * 0.8})`;
                    ctx.lineWidth = 0.8 + Math.random() * 2.6;
                    bctx.strokeStyle = dark ? `rgba(90, 90, 90, ${alpha * 1.4})` : `rgba(165, 165, 165, ${alpha})`;
                    bctx.lineWidth = ctx.lineWidth;

                    ctx.beginPath();
                    bctx.beginPath();
                    let wy = gy;
                    ctx.moveTo(0, wy);
                    bctx.moveTo(0, wy);
                    for (let x = 32; x <= 1024; x += 32) {
                        wy += Math.sin(x * 0.012 + p * 2.1 + i * 0.2) * 2.2;
                        wy = Math.max(y0 + 2, Math.min(y0 + plankH - 2, wy));
                        ctx.lineTo(x, wy);
                        bctx.lineTo(x, wy);
                    }
                    ctx.stroke();
                    bctx.stroke();
                }

                // Subtle wood knot
                if (p === 1 || p === 3) {
                    const kx = 260 + p * 170;
                    const ky = y0 + plankH * 0.52;
                    const grad = ctx.createRadialGradient(kx, ky, 3, kx, ky, 34);
                    grad.addColorStop(0, 'rgba(52, 24, 9, 0.65)');
                    grad.addColorStop(0.55, 'rgba(84, 44, 18, 0.28)');
                    grad.addColorStop(1, 'rgba(154, 98, 52, 0)');
                    ctx.fillStyle = grad;
                    ctx.beginPath();
                    ctx.ellipse(kx, ky, 46, 22, 0.08, 0, Math.PI * 2);
                    ctx.fill();
                }

                // Seam line
                ctx.fillStyle = 'rgba(46, 22, 8, 0.35)';
                ctx.fillRect(0, y0, 1024, 2);
                bctx.fillStyle = '#383838';
                bctx.fillRect(0, y0, 1024, 3);
            }

            const colorTex = new THREE.CanvasTexture(canvas);
            colorTex.colorSpace = THREE.SRGBColorSpace;
            colorTex.anisotropy = 8;

            const bumpTex = new THREE.CanvasTexture(bumpCanvas);
            bumpTex.anisotropy = 4;

            return { colorTex, bumpTex };
        }

        function createCheesePasteTexture(baseHex, fleckHex = null, veinsHex = null) {
            const canvas = document.createElement('canvas');
            canvas.width = 512;
            canvas.height = 512;
            const ctx = canvas.getContext('2d');

            ctx.fillStyle = baseHex;
            ctx.fillRect(0, 0, 512, 512);

            // Soft organic mottling
            for (let i = 0; i < 180; i++) {
                const x = Math.random() * 512;
                const y = Math.random() * 512;
                const r = 10 + Math.random() * 38;
                const grad = ctx.createRadialGradient(x, y, 1, x, y, r);
                grad.addColorStop(0, Math.random() > 0.5 ? 'rgba(255,250,230,0.08)' : 'rgba(160,105,20,0.06)');
                grad.addColorStop(1, 'rgba(0,0,0,0)');
                ctx.fillStyle = grad;
                ctx.beginPath();
                ctx.arc(x, y, r, 0, Math.PI * 2);
                ctx.fill();
            }

            // Ripening crystals (for Oude Gouda)
            if (fleckHex) {
                ctx.fillStyle = fleckHex;
                for (let i = 0; i < 220; i++) {
                    const x = Math.random() * 512;
                    const y = Math.random() * 512;
                    const s = 1 + Math.random() * 2.3;
                    ctx.globalAlpha = 0.35 + Math.random() * 0.45;
                    ctx.beginPath();
                    ctx.arc(x, y, s, 0, Math.PI * 2);
                    ctx.fill();
                }
                ctx.globalAlpha = 1;
            }

            // Blue cheese veins (for Roquefort)
            if (veinsHex) {
                for (let v = 0; v < 34; v++) {
                    let vx = Math.random() * 512;
                    let vy = Math.random() * 512;
                    let angle = Math.random() * Math.PI * 2;
                    ctx.strokeStyle = veinsHex;
                    ctx.lineCap = 'round';
                    ctx.beginPath();
                    ctx.moveTo(vx, vy);
                    const steps = 8 + Math.floor(Math.random() * 12);
                    for (let s = 0; s < steps; s++) {
                        angle += (Math.random() - 0.5) * 1.3;
                        vx += Math.cos(angle) * (6 + Math.random() * 10);
                        vy += Math.sin(angle) * (6 + Math.random() * 10);
                        ctx.lineWidth = 1.2 + Math.random() * 3.4;
                        ctx.globalAlpha = 0.45 + Math.random() * 0.4;
                        ctx.lineTo(vx, vy);
                    }
                    ctx.stroke();

                    // Crumbly pockets along veins
                    ctx.fillStyle = veinsHex;
                    ctx.beginPath();
                    ctx.arc(vx, vy, 2 + Math.random() * 4.5, 0, Math.PI * 2);
                    ctx.fill();
                }
                ctx.globalAlpha = 1;
            }

            const tex = new THREE.CanvasTexture(canvas);
            tex.colorSpace = THREE.SRGBColorSpace;
            tex.wrapS = THREE.RepeatWrapping;
            tex.wrapT = THREE.RepeatWrapping;
            return tex;
        }

        function createBrieRindTexture() {
            const canvas = document.createElement('canvas');
            canvas.width = 512;
            canvas.height = 512;
            const ctx = canvas.getContext('2d');

            ctx.fillStyle = '#f5f0e6';
            ctx.fillRect(0, 0, 512, 512);

            // Subtle maturation rack ridges & creamy blush
            for (let y = 0; y < 512; y += 10) {
                ctx.fillStyle = 'rgba(218, 198, 166, 0.16)';
                ctx.fillRect(0, y, 512, 3);
            }
            for (let i = 0; i < 240; i++) {
                const x = Math.random() * 512;
                const y = Math.random() * 512;
                const r = 6 + Math.random() * 24;
                ctx.fillStyle = Math.random() > 0.3 ? 'rgba(255,255,255,0.18)' : 'rgba(210,185,148,0.12)';
                ctx.beginPath();
                ctx.arc(x, y, r, 0, Math.PI * 2);
                ctx.fill();
            }

            const tex = new THREE.CanvasTexture(canvas);
            tex.colorSpace = THREE.SRGBColorSpace;
            return tex;
        }

        const { colorTex: woodTex, bumpTex: woodBump } = createWoodTextures();

        // --- Root Turntable Group (rotates smoothly when idle) ---
        const boardGroup = new THREE.Group();
        scene.add(boardGroup);

        // Subtle dark slate base mat under the wooden feet (catches soft shadow cleanly from every angle)
        const basePadGeo = new THREE.CylinderGeometry(5.05, 5.2, 0.08, 64);
        const basePadMat = new THREE.MeshStandardMaterial({
            color: 0x1c1512,
            roughness: 0.92,
            metalness: 0.05
        });
        const basePad = new THREE.Mesh(basePadGeo, basePadMat);
        basePad.position.y = -0.66;
        basePad.receiveShadow = true;
        scene.add(basePad);

        // --- Build Artisanal Wooden Kaasplank ---
        function buildWoodenBoard() {
            const woodMat = new THREE.MeshStandardMaterial({
                map: woodTex,
                bumpMap: woodBump,
                bumpScale: 0.025,
                roughness: 0.62,
                metalness: 0.04
            });

            const darkGrooveMat = new THREE.MeshStandardMaterial({
                color: 0x693e1d,
                roughness: 0.78
            });

            // Main board shape with integrated paddle handle (extruded in XY then rotated flat to XZ)
            const R = 4.65;
            const handleAngle = 0.138;
            const boardShape = new THREE.Shape();
            boardShape.absarc(0, 0, R, handleAngle, Math.PI * 2 - handleAngle, false);
            // Smooth neck out to paddle handle on +X
            boardShape.quadraticCurveTo(4.85, -0.58, 5.55, -0.56);
            boardShape.absarc(5.65, 0, 0.56, -Math.PI / 2, Math.PI / 2, false);
            boardShape.quadraticCurveTo(4.85, 0.58, R * Math.cos(handleAngle), R * Math.sin(handleAngle));

            // Hanging hole in handle
            const hangHole = new THREE.Path();
            hangHole.absarc(5.68, 0, 0.19, 0, Math.PI * 2, true);
            boardShape.holes.push(hangHole);

            const extrudeOpts = {
                depth: 0.34,
                bevelEnabled: true,
                bevelThickness: 0.05,
                bevelSize: 0.05,
                bevelSegments: 5,
                curveSegments: 48
            };
            const boardGeo = new THREE.ExtrudeGeometry(boardShape, extrudeOpts);
            // Normalize planar UVs so wood grain runs along the board & handle
            const posAttr = boardGeo.attributes.position;
            const uvAttr = boardGeo.attributes.uv;
            for (let i = 0; i < posAttr.count; i++) {
                const x = posAttr.getX(i);
                const y = posAttr.getY(i);
                uvAttr.setXY(i, (x + 4.9) / 11.4, (y + 4.9) / 9.8);
            }
            uvAttr.needsUpdate = true;

            const boardMesh = new THREE.Mesh(boardGeo, woodMat);
            boardMesh.rotation.x = -Math.PI / 2;
            boardMesh.position.y = -0.39;
            boardMesh.castShadow = true;
            boardMesh.receiveShadow = true;
            boardGroup.add(boardMesh);

            // Subtle juice groove ring on top surface
            const grooveGeo = new THREE.RingGeometry(4.16, 4.26, 72);
            const grooveMesh = new THREE.Mesh(grooveGeo, darkGrooveMat);
            grooveMesh.rotation.x = -Math.PI / 2;
            grooveMesh.position.y = 0.002;
            grooveMesh.receiveShadow = true;
            boardGroup.add(grooveMesh);

            // Rustic twine loop through the handle hole
            const cordCurve = new THREE.CatmullRomCurve3([
                new THREE.Vector3(5.85, -0.18, 0.0),
                new THREE.Vector3(6.25, -0.22, 0.22),
                new THREE.Vector3(6.52, -0.26, 0.0),
                new THREE.Vector3(6.25, -0.22, -0.22),
                new THREE.Vector3(5.85, -0.18, 0.0)
            ], true);
            const cordGeo = new THREE.TubeGeometry(cordCurve, 32, 0.035, 8, true);
            const cordMat = new THREE.MeshStandardMaterial({ color: 0xc29b68, roughness: 0.9 });
            const cordMesh = new THREE.Mesh(cordGeo, cordMat);
            cordMesh.castShadow = true;
            boardGroup.add(cordMesh);

            // 4 turned wooden feet underneath the plank (visible in Vooraanzicht!)
            const footGeo = new THREE.CylinderGeometry(0.32, 0.24, 0.24, 24);
            for (let i = 0; i < 4; i++) {
                const ang = (i * Math.PI) / 2 + Math.PI / 4;
                const foot = new THREE.Mesh(footGeo, darkGrooveMat);
                foot.position.set(Math.cos(ang) * 3.25, -0.53, Math.sin(ang) * 3.25);
                foot.castShadow = true;
                foot.receiveShadow = true;
                boardGroup.add(foot);
            }
        }
        buildWoodenBoard();

        function createHoleShadowTexture(rimHex) {
            const canvas = document.createElement('canvas');
            canvas.width = 128;
            canvas.height = 256;
            const ctx = canvas.getContext('2d');
            // v=1 (top of canvas) = deep inside hole cup; v=0 (bottom of canvas) = hole rim
            const grad = ctx.createLinearGradient(0, 0, 0, 256);
            grad.addColorStop(0.0, '#1e1005');
            grad.addColorStop(0.45, '#47280a');
            grad.addColorStop(0.82, '#8f5d16');
            grad.addColorStop(1.0, rimHex);
            ctx.fillStyle = grad;
            ctx.fillRect(0, 0, 128, 256);
            const tex = new THREE.CanvasTexture(canvas);
            tex.colorSpace = THREE.SRGBColorSpace;
            return tex;
        }

        // --- Cheese Registry & Occluder Meshes ---
        const cheeses = [];
        const interactiveCheeseMeshes = [];
        const cheeseOccluderMeshes = [];

        /**
         * Helper to register a verified collision-free side-peek spot beside a solid cheese.
         * - peekAnchor sits just outside a corner/edge of the cheese (+Z = peek outward direction).
         * - behindProbe sits tucked behind the cheese body so we can raycast-verify camera occlusion!
         * - cheeseInteriorLocal (in parent coords) determines unambiguously which side of peekAnchor hugs the cheese!
         */
        function makeSidePeekSpot(parent, px, pz, rotY, behindLocalX, behindLocalZ, cheeseInteriorX = 0, cheeseInteriorZ = 0) {
            const peekAnchor = new THREE.Object3D();
            peekAnchor.position.set(px, 0, pz);
            peekAnchor.rotation.y = rotY;

            const behindProbe = new THREE.Object3D();
            behindProbe.position.set(behindLocalX, 0.22, behindLocalZ);

            parent.add(peekAnchor, behindProbe);

            // Vector from peekAnchor to cheese interior projected onto peekAnchor's local +X axis:
            // In parent XZ, peekAnchor's +X axis under rotation.y = rotY is (cos(rotY), 0, -sin(rotY))
            const dx = cheeseInteriorX - px;
            const dz = cheeseInteriorZ - pz;
            const localCheeseX = dx * Math.cos(rotY) - dz * Math.sin(rotY);
            const cheeseSide = localCheeseX >= 0 ? 1 : -1;

            return { peekAnchor, behindProbe, cheeseSide };
        }

        /**
         * Builds a flat 2D face with real punched circular holes + 3D concave inner cups!
         * Local coordinates of returned Group:
         * - Face lies in XY plane (z = 0)
         * - Outward normal is +Z
         * - Concave cheese hole interiors extend into -Z
         */
        function buildPunchedFace(shape, holes, faceMat, holeCupMat, uvScale = 0.35) {
            const faceGroup = new THREE.Group();

            for (const h of holes) {
                const path = new THREE.Path();
                path.absarc(h.u, h.v, h.r, 0, Math.PI * 2, true);
                shape.holes.push(path);

                // Deep shadowed concave inner cup behind the hole
                const depthScale = h.mouseCapable ? 1.52 : 0.82;
                const cupGeo = new THREE.SphereGeometry(h.r, 24, 16, 0, Math.PI * 2, 0, Math.PI / 2);
                const cupMesh = new THREE.Mesh(cupGeo, holeCupMat);
                // Rotate northern hemisphere (+Y) to -Z (into the cheese)
                cupMesh.rotation.x = -Math.PI / 2;
                cupMesh.scale.set(1, 1, depthScale);
                cupMesh.position.set(h.u, h.v, 0);
                cupMesh.receiveShadow = true;
                faceGroup.add(cupMesh);
            }

            const geo = new THREE.ShapeGeometry(shape, 28);
            const pos = geo.attributes.position;
            const uv = geo.attributes.uv;
            for (let i = 0; i < pos.count; i++) {
                uv.setXY(i, pos.getX(i) * uvScale, pos.getY(i) * uvScale);
            }
            uv.needsUpdate = true;

            const mesh = new THREE.Mesh(geo, faceMat);
            mesh.castShadow = true;
            mesh.receiveShadow = true;
            faceGroup.add(mesh);

            return { faceGroup, mainMesh: mesh };
        }

        /**
         * Creates a 3D cheese wedge (either with real geometric holes or solid).
         * Apex starts at (0, 0, 0) extending along +X to radius R with half-angle alpha,
         * then centered around its visual centroid inside cheeseGroup.
         */
        function createCheeseWedge({
            name,
            subtitle,
            hasHoles,
            R,
            H,
            alpha,
            pasteMat,
            rindMat,
            holeCupMat = null,
            side1Holes = [],
            side2Holes = [],
            topHoles = [],
            position,
            rotY = 0
        }) {
            const cheeseGroup = new THREE.Group();
            cheeseGroup.position.copy(position);
            cheeseGroup.rotation.y = rotY;
            boardGroup.add(cheeseGroup);

            // Shift inner geometry so wedge centroid sits near (0, 0, 0) of cheeseGroup
            const centroidOffset = -R * 0.56;
            const wedgeInner = new THREE.Group();
            wedgeInner.position.set(centroidOffset, 0, 0);
            cheeseGroup.add(wedgeInner);

            const holeSpots = [];
            const sidePeekSpots = [];
            const meshesForPicking = [];

            // 1. Top triangular sector face (at y = H, rotated -PI/2 around X)
            const topShape = new THREE.Shape();
            topShape.moveTo(0, 0);
            topShape.lineTo(R * Math.cos(alpha), -R * Math.sin(alpha));
            topShape.absarc(0, 0, R, -alpha, alpha, false);
            topShape.closePath();

            const topBuilt = buildPunchedFace(
                topShape,
                hasHoles ? topHoles : [],
                pasteMat,
                holeCupMat || pasteMat
            );
            topBuilt.faceGroup.position.set(0, H, 0);
            topBuilt.faceGroup.rotation.x = -Math.PI / 2;
            wedgeInner.add(topBuilt.faceGroup);
            meshesForPicking.push(topBuilt.mainMesh);

            // Register mouse-capable top holes
            if (hasHoles) {
                for (const h of topHoles) {
                    if (!h.mouseCapable) continue;
                    const anchor = new THREE.Object3D();
                    // In topBuilt.faceGroup local space: hole is at (h.u, h.v, 0) with outward normal +Z!
                    // For a top hole, rotate around local Z so mouse belly (-Y) faces toward wedge tip (-X)
                    anchor.position.set(h.u, h.v, 0);
                    anchor.rotation.z = -Math.PI / 2;
                    topBuilt.faceGroup.add(anchor);
                    holeSpots.push({ anchor, radius: h.r, isTopHole: true });
                }
            }

            // 2. Bottom triangular sector face (solid, at y = 0, rotated +PI/2 around X)
            const botShape = new THREE.Shape();
            botShape.moveTo(0, 0);
            botShape.lineTo(R * Math.cos(alpha), -R * Math.sin(alpha));
            botShape.absarc(0, 0, R, -alpha, alpha, false);
            botShape.closePath();
            const botGeo = new THREE.ShapeGeometry(botShape, 20);
            const botMesh = new THREE.Mesh(botGeo, rindMat);
            botMesh.rotation.x = Math.PI / 2;
            botMesh.position.y = 0.004;
            botMesh.castShadow = true;
            wedgeInner.add(botMesh);

            // 3. Side 2 (+Z cut face: runs from apex u=0 to rind u=R at angle +alpha)
            const side2Shape = new THREE.Shape();
            side2Shape.moveTo(0, 0);
            side2Shape.lineTo(R, 0);
            side2Shape.lineTo(R, H);
            side2Shape.lineTo(0, H);
            side2Shape.closePath();

            const side2Built = buildPunchedFace(
                side2Shape,
                hasHoles ? side2Holes : [],
                pasteMat,
                holeCupMat || pasteMat
            );
            side2Built.faceGroup.rotation.y = -alpha;
            wedgeInner.add(side2Built.faceGroup);
            meshesForPicking.push(side2Built.mainMesh);

            if (hasHoles) {
                for (const h of side2Holes) {
                    if (!h.mouseCapable) continue;
                    const anchor = new THREE.Object3D();
                    anchor.position.set(h.u, h.v, 0);
                    side2Built.faceGroup.add(anchor);
                    holeSpots.push({ anchor, radius: h.r, isTopHole: false });
                }
            }

            // 4. Side 1 (-Z cut face: runs from rind u=0 at C1 to apex u=R)
            const side1Shape = new THREE.Shape();
            side1Shape.moveTo(0, 0);
            side1Shape.lineTo(R, 0);
            side1Shape.lineTo(R, H);
            side1Shape.lineTo(0, H);
            side1Shape.closePath();

            const side1Built = buildPunchedFace(
                side1Shape,
                hasHoles ? side1Holes : [],
                pasteMat,
                holeCupMat || pasteMat
            );
            side1Built.faceGroup.position.set(R * Math.cos(alpha), 0, -R * Math.sin(alpha));
            side1Built.faceGroup.rotation.y = Math.PI + alpha;
            wedgeInner.add(side1Built.faceGroup);
            meshesForPicking.push(side1Built.mainMesh);

            if (hasHoles) {
                for (const h of side1Holes) {
                    if (!h.mouseCapable) continue;
                    const anchor = new THREE.Object3D();
                    anchor.position.set(h.u, h.v, 0);
                    side1Built.faceGroup.add(anchor);
                    holeSpots.push({ anchor, radius: h.r, isTopHole: false });
                }
            }

            // 5. Curved outer rind back
            const rindGeo = new THREE.CylinderGeometry(
                R, R, H, 32, 1, true,
                Math.PI / 2 - alpha, 2 * alpha
            );
            const rindMesh = new THREE.Mesh(rindGeo, rindMat);
            rindMesh.position.set(0, H / 2, 0);
            rindMesh.castShadow = true;
            rindMesh.receiveShadow = true;
            wedgeInner.add(rindMesh);
            meshesForPicking.push(rindMesh);

            // Register collision-free side-peek spots around the wedge corners & rind
            if (!hasHoles) {
                const cX = R * Math.cos(alpha);
                const cZ = R * Math.sin(alpha);
                const midX = R * 0.58;
                // Spot 1: peeking forward past +Z corner along Side 2
                sidePeekSpots.push(makeSidePeekSpot(
                    wedgeInner,
                    cX - 0.12, cZ + 0.22,
                    Math.PI / 2 - alpha + 0.2,
                    R + 0.16, cZ * 0.42,
                    midX, 0
                ));
                // Spot 2: peeking forward past -Z corner along Side 1
                sidePeekSpots.push(makeSidePeekSpot(
                    wedgeInner,
                    cX - 0.12, -cZ - 0.22,
                    -Math.PI / 2 + alpha - 0.2,
                    R + 0.16, -cZ * 0.42,
                    midX, 0
                ));
                // Spot 3: peeking around curved rind toward +Z
                sidePeekSpots.push(makeSidePeekSpot(
                    wedgeInner,
                    R + 0.20, cZ * 0.45,
                    0.15,
                    cX - 0.25, -cZ - 0.18,
                    midX, 0
                ));
            }

            const cheeseData = {
                name,
                subtitle,
                hasHoles,
                group: cheeseGroup,
                holeSpots,
                sidePeekSpots
            };

            for (const m of meshesForPicking) {
                m.userData.cheese = cheeseData;
                interactiveCheeseMeshes.push(m);
                cheeseOccluderMeshes.push(m);
            }

            cheeses.push(cheeseData);
            return cheeseData;
        }

        // --- Shared & Specific Cheese Materials ---
        const emmentalPasteMat = new THREE.MeshStandardMaterial({
            map: createCheesePasteTexture('#f6cb52'),
            roughness: 0.52,
            metalness: 0.02
        });
        const emmentalRindMat = new THREE.MeshStandardMaterial({
            color: 0xc98a2c,
            roughness: 0.72
        });
        const emmentalHoleMat = new THREE.MeshStandardMaterial({
            map: createHoleShadowTexture('#d69a28'),
            roughness: 0.72,
            side: THREE.BackSide
        });

        const maasdamPasteMat = new THREE.MeshStandardMaterial({
            map: createCheesePasteTexture('#fae07f'),
            roughness: 0.48,
            metalness: 0.02
        });
        const maasdamRindMat = new THREE.MeshStandardMaterial({
            color: 0xdfa234,
            roughness: 0.42
        });
        const maasdamHoleMat = new THREE.MeshStandardMaterial({
            map: createHoleShadowTexture('#d9a838'),
            roughness: 0.68,
            side: THREE.BackSide
        });

        const goudaPasteMat = new THREE.MeshStandardMaterial({
            map: createCheesePasteTexture('#df9126', '#fff5d6'),
            roughness: 0.55
        });
        const goudaWaxMat = new THREE.MeshStandardMaterial({
            color: 0x2b1712,
            roughness: 0.34,
            metalness: 0.04
        });

        const bluePasteMat = new THREE.MeshStandardMaterial({
            map: createCheesePasteTexture('#f2ebd9', null, '#355e58'),
            roughness: 0.68
        });
        const blueRindMat = new THREE.MeshStandardMaterial({
            color: 0xd8d0bc,
            roughness: 0.82
        });

        // 1. Emmentaler (HAS HOLES)
        createCheeseWedge({
            name: 'Emmentaler',
            subtitle: 'Zwitserse gatenkaas • muis komt uit een gat',
            hasHoles: true,
            R: 2.12,
            H: 1.16,
            alpha: 0.38,
            pasteMat: emmentalPasteMat,
            rindMat: emmentalRindMat,
            holeCupMat: emmentalHoleMat,
            side2Holes: [
                { u: 1.52, v: 0.54, r: 0.23, mouseCapable: true },
                { u: 0.98, v: 0.68, r: 0.21, mouseCapable: true },
                { u: 1.84, v: 0.88, r: 0.11, mouseCapable: false },
                { u: 1.28, v: 0.24, r: 0.10, mouseCapable: false },
                { u: 0.56, v: 0.38, r: 0.09, mouseCapable: false }
            ],
            side1Holes: [
                { u: 0.60, v: 0.56, r: 0.23, mouseCapable: true },
                { u: 1.12, v: 0.44, r: 0.12, mouseCapable: false },
                { u: 0.34, v: 0.86, r: 0.11, mouseCapable: false },
                { u: 1.44, v: 0.78, r: 0.09, mouseCapable: false }
            ],
            topHoles: [
                { u: 1.48, v: 0.04, r: 0.23, mouseCapable: true },
                { u: 1.08, v: -0.22, r: 0.11, mouseCapable: false },
                { u: 1.72, v: -0.32, r: 0.10, mouseCapable: false },
                { u: 0.80, v: 0.14, r: 0.08, mouseCapable: false }
            ],
            position: new THREE.Vector3(-1.62, 0, -1.22),
            rotY: 0.52
        });

        // 2. Maasdammer (HAS HOLES)
        createCheeseWedge({
            name: 'Maasdammer',
            subtitle: 'Romige gatenkaas • muis komt uit een gat',
            hasHoles: true,
            R: 1.84,
            H: 1.04,
            alpha: 0.43,
            pasteMat: maasdamPasteMat,
            rindMat: maasdamRindMat,
            holeCupMat: maasdamHoleMat,
            side2Holes: [
                { u: 1.32, v: 0.52, r: 0.23, mouseCapable: true },
                { u: 0.82, v: 0.68, r: 0.11, mouseCapable: false },
                { u: 1.58, v: 0.24, r: 0.09, mouseCapable: false }
            ],
            side1Holes: [
                { u: 0.55, v: 0.50, r: 0.22, mouseCapable: true },
                { u: 1.02, v: 0.34, r: 0.11, mouseCapable: false },
                { u: 0.30, v: 0.82, r: 0.09, mouseCapable: false }
            ],
            topHoles: [
                { u: 1.28, v: 0.02, r: 0.22, mouseCapable: true },
                { u: 0.90, v: 0.20, r: 0.10, mouseCapable: false },
                { u: 1.50, v: -0.30, r: 0.09, mouseCapable: false }
            ],
            position: new THREE.Vector3(1.58, 0, 1.22),
            rotY: -2.48
        });

        // 3. Oude Boerenkaas / Gouda (NO HOLES)
        createCheeseWedge({
            name: 'Oude Boerenkaas',
            subtitle: 'Zonder gaten • muis piept langs het kaasje',
            hasHoles: false,
            R: 1.94,
            H: 0.78,
            alpha: 0.30,
            pasteMat: goudaPasteMat,
            rindMat: goudaWaxMat,
            position: new THREE.Vector3(1.72, 0, -1.32),
            rotY: 2.22
        });

        // 4. Roquefort Blauwe Aderkaas (NO HOLES)
        createCheeseWedge({
            name: 'Roquefort',
            subtitle: 'Blauwe kaas zonder gaten • muis piept langs het kaasje',
            hasHoles: false,
            R: 1.36,
            H: 0.92,
            alpha: 0.36,
            pasteMat: bluePasteMat,
            rindMat: blueRindMat,
            position: new THREE.Vector3(-0.12, 0, 2.48),
            rotY: -0.92
        });

        // 5. Brie de Meaux Wheel + Cut Slice (NO HOLES)
        function buildBrieCheese() {
            const brieGroup = new THREE.Group();
            brieGroup.position.set(-1.88, 0, 1.28);
            brieGroup.rotation.y = 0.35;
            boardGroup.add(brieGroup);

            const brieRindMat = new THREE.MeshStandardMaterial({
                map: createBrieRindTexture(),
                roughness: 0.85
            });
            const brieCreamMat = new THREE.MeshStandardMaterial({
                map: createCheesePasteTexture('#fcf2cf'),
                roughness: 0.32
            });

            const R = 1.02;
            const H = 0.52;
            const missingAngle = 0.72; // ~41 deg slice cut out

            const wheelShape = new THREE.Shape();
            wheelShape.moveTo(0, 0);
            wheelShape.absarc(0, 0, R, missingAngle / 2, Math.PI * 2 - missingAngle / 2, false);
            wheelShape.closePath();

            const wheelGeo = new THREE.ExtrudeGeometry(wheelShape, {
                depth: H - 0.06,
                bevelEnabled: true,
                bevelThickness: 0.03,
                bevelSize: 0.03,
                bevelSegments: 4,
                curveSegments: 40
            });
            const wheelMesh = new THREE.Mesh(wheelGeo, brieRindMat);
            wheelMesh.rotation.x = -Math.PI / 2;
            wheelMesh.position.y = 0.03;
            wheelMesh.castShadow = true;
            wheelMesh.receiveShadow = true;
            brieGroup.add(wheelMesh);

            const brieCreamDoubleMat = brieCreamMat.clone();
            brieCreamDoubleMat.side = THREE.DoubleSide;
            const cutPlaneGeo = new THREE.PlaneGeometry(R - 0.02, H - 0.04);
            const cutFace1 = new THREE.Mesh(cutPlaneGeo, brieCreamDoubleMat);
            cutFace1.position.set(
                Math.cos(missingAngle / 2) * (R * 0.49),
                H * 0.5,
                -Math.sin(missingAngle / 2) * (R * 0.49)
            );
            cutFace1.rotation.y = missingAngle / 2;
            brieGroup.add(cutFace1);

            const cutFace2 = new THREE.Mesh(cutPlaneGeo, brieCreamDoubleMat);
            cutFace2.position.set(
                Math.cos(missingAngle / 2) * (R * 0.49),
                H * 0.5,
                Math.sin(missingAngle / 2) * (R * 0.49)
            );
            cutFace2.rotation.y = -missingAngle / 2;
            brieGroup.add(cutFace2);

            // Pulled-out Brie slice resting slightly forward
            const sliceShape = new THREE.Shape();
            sliceShape.moveTo(0, 0);
            sliceShape.absarc(0, 0, R * 0.94, -missingAngle * 0.42, missingAngle * 0.42, false);
            sliceShape.closePath();
            const sliceGeo = new THREE.ExtrudeGeometry(sliceShape, {
                depth: H - 0.08,
                bevelEnabled: true,
                bevelThickness: 0.02,
                bevelSize: 0.02,
                bevelSegments: 3
            });
            const sliceMesh = new THREE.Mesh(sliceGeo, [brieRindMat, brieCreamMat]);
            sliceMesh.rotation.x = -Math.PI / 2;
            sliceMesh.rotation.z = -0.18;
            sliceMesh.position.set(0.38, 0.02, 0.14);
            sliceMesh.castShadow = true;
            sliceMesh.receiveShadow = true;
            brieGroup.add(sliceMesh);

            // 3 collision-free side-peek spots around the Brie wheel
            const dist = R + 0.21;
            const sidePeekSpots = [
                { ang: 1.55, sgn: 1, bAng: 2.35 },
                { ang: -1.55, sgn: -1, bAng: -2.35 },
                { ang: -2.45, sgn: -1, bAng: 2.95 }
            ].map(({ ang, sgn, bAng }) => {
                const px = Math.cos(ang) * dist;
                const pz = -Math.sin(ang) * dist;
                const rx = Math.cos(ang), rz = -Math.sin(ang);
                const tx = -rz * sgn, tz = rx * sgn;
                const fx = rx * 0.55 + tx * 0.835;
                const fz = rz * 0.55 + tz * 0.835;
                const rotY = Math.atan2(fx, fz);
                return makeSidePeekSpot(
                    brieGroup,
                    px, pz, rotY,
                    Math.cos(bAng) * (R + 0.16), -Math.sin(bAng) * (R + 0.16),
                    0, 0
                );
            });

            const brieData = {
                name: 'Brie de Meaux',
                subtitle: 'Witschimmelkaas zonder gaten • muis piept langs het kaasje',
                hasHoles: false,
                group: brieGroup,
                holeSpots: [],
                sidePeekSpots
            };

            wheelMesh.userData.cheese = brieData;
            sliceMesh.userData.cheese = brieData;
            interactiveCheeseMeshes.push(wheelMesh, sliceMesh);
            cheeseOccluderMeshes.push(wheelMesh, sliceMesh);
            cheeses.push(brieData);
        }
        buildBrieCheese();

        // 6. Edammer Bolletje in Red Wax (NO HOLES)
        function buildEdammerBall() {
            const edamGroup = new THREE.Group();
            edamGroup.position.set(-0.05, 0, -2.72);
            edamGroup.rotation.y = -0.35;
            boardGroup.add(edamGroup);

            const redWaxMat = new THREE.MeshStandardMaterial({
                color: 0xc81d25,
                roughness: 0.28,
                metalness: 0.05
            });
            const edamInsideMat = new THREE.MeshStandardMaterial({
                map: createCheesePasteTexture('#f7c948'),
                roughness: 0.45,
                side: THREE.DoubleSide
            });

            const R = 0.74;
            const cutAngle = 0.78;

            const profilePts = [];
            profilePts.push(new THREE.Vector2(0, 0.01));
            const steps = 20;
            for (let i = 0; i <= steps; i++) {
                const theta = -Math.PI * 0.36 + (i / steps) * (Math.PI * 0.86);
                const r = Math.max(0, Math.cos(theta) * R);
                const y = (Math.sin(theta) + Math.sin(Math.PI * 0.36)) * (R * 0.68) + 0.01;
                profilePts.push(new THREE.Vector2(r, y));
            }
            profilePts.push(new THREE.Vector2(0, profilePts[profilePts.length - 1].y));

            const outerGeo = new THREE.LatheGeometry(profilePts, 36, cutAngle, Math.PI * 2 - cutAngle);
            const outerMesh = new THREE.Mesh(outerGeo, redWaxMat);
            outerMesh.castShadow = true;
            outerMesh.receiveShadow = true;
            edamGroup.add(outerMesh);

            const cutProfileShape = new THREE.Shape(profilePts);
            const cutFaceGeo = new THREE.ShapeGeometry(cutProfileShape, 20);
            const cutA = new THREE.Mesh(cutFaceGeo, edamInsideMat);
            cutA.rotation.y = -Math.PI / 2;
            edamGroup.add(cutA);

            const cutB = new THREE.Mesh(cutFaceGeo, edamInsideMat);
            cutB.rotation.y = -Math.PI / 2 + cutAngle;
            edamGroup.add(cutB);

            // 3 collision-free side-peek spots around the outer half of Edammer (in edamGroup local space)
            const dist = R + 0.21;
            const sidePeekSpots = [
                { boardAng: -0.75, sgn: 1, bBoardAng: -1.65 },
                { boardAng: -1.55, sgn: 1, bBoardAng: -2.45 },
                { boardAng: -2.25, sgn: -1, bBoardAng: -1.35 }
            ].map(({ boardAng, sgn, bBoardAng }) => {
                // Convert desired angle in board space by compensating edamGroup.rotation.y (-0.35)
                const localAng = boardAng + 0.35;
                const bLocalAng = bBoardAng + 0.35;
                const px = Math.cos(localAng) * dist;
                const pz = Math.sin(localAng) * dist;
                const rx = Math.cos(localAng), rz = Math.sin(localAng);
                const tx = -rz * sgn, tz = rx * sgn;
                const fx = rx * 0.55 + tx * 0.835;
                const fz = rz * 0.55 + tz * 0.835;
                const rotY = Math.atan2(fx, fz);
                return makeSidePeekSpot(
                    edamGroup,
                    px, pz, rotY,
                    Math.cos(bLocalAng) * (R + 0.15), Math.sin(bLocalAng) * (R + 0.15),
                    0, 0
                );
            });

            const edamData = {
                name: 'Edammer Bolletje',
                subtitle: 'Rode waskorst zonder gaten • muis piept langs het kaasje',
                hasHoles: false,
                group: edamGroup,
                holeSpots: [],
                sidePeekSpots
            };

            outerMesh.userData.cheese = edamData;
            cutA.userData.cheese = edamData;
            cutB.userData.cheese = edamData;
            interactiveCheeseMeshes.push(outerMesh, cutA, cutB);
            cheeseOccluderMeshes.push(outerMesh, cutA, cutB);
            cheeses.push(edamData);
        }
        buildEdammerBall();

        // --- Bourgondische Garnishes (placed strictly outside all mouse lanes!) ---
        function buildGarnishes() {
            // 1. Cluster of purple grapes in the center
            const grapeGroup = new THREE.Group();
            grapeGroup.position.set(-0.02, 0, 0.02);
            boardGroup.add(grapeGroup);

            const grapeMat = new THREE.MeshStandardMaterial({
                color: 0x4f193d,
                roughness: 0.22,
                metalness: 0.06
            });
            const grapeMatAlt = new THREE.MeshStandardMaterial({
                color: 0x67224e,
                roughness: 0.25,
                metalness: 0.05
            });
            const grapeGeo = new THREE.SphereGeometry(0.14, 16, 14);

            const grapeCoords = [
                [0, 0.14, 0], [0.20, 0.14, 0.07], [-0.19, 0.14, 0.10], [0.05, 0.14, -0.21],
                [-0.17, 0.14, -0.16], [0.24, 0.13, -0.14], [0.04, 0.13, 0.24], [-0.32, 0.13, -0.02],
                [0.36, 0.13, 0.02], [-0.07, 0.13, 0.36], [0.16, 0.13, 0.31],
                [0.08, 0.30, 0.04], [-0.11, 0.29, -0.05], [0.14, 0.28, -0.11], [-0.05, 0.28, 0.17],
                [0.01, 0.43, 0.02]
            ];
            grapeCoords.forEach(([gx, gy, gz], idx) => {
                const g = new THREE.Mesh(grapeGeo, idx % 2 === 0 ? grapeMat : grapeMatAlt);
                g.position.set(gx, gy, gz);
                g.scale.set(1, 0.92, 1.08);
                g.castShadow = true;
                grapeGroup.add(g);
            });

            const stemCurve = new THREE.CatmullRomCurve3([
                new THREE.Vector3(-0.16, 0.46, -0.07),
                new THREE.Vector3(0.02, 0.54, 0.02),
                new THREE.Vector3(0.20, 0.64, 0.09)
            ]);
            const stemGeo = new THREE.TubeGeometry(stemCurve, 12, 0.022, 6, false);
            const stemMat = new THREE.MeshStandardMaterial({ color: 0x4b5d2a, roughness: 0.85 });
            grapeGroup.add(new THREE.Mesh(stemGeo, stemMat));

            // 2. Small ceramic bowl of golden honey / fig jam
            const bowlGroup = new THREE.Group();
            bowlGroup.position.set(3.35, 0, 0.05);
            boardGroup.add(bowlGroup);

            const bowlPoints = [
                new THREE.Vector2(0.0, 0.0),
                new THREE.Vector2(0.40, 0.0),
                new THREE.Vector2(0.53, 0.35),
                new THREE.Vector2(0.47, 0.37),
                new THREE.Vector2(0.36, 0.08),
                new THREE.Vector2(0.0, 0.08)
            ];
            const bowlGeo = new THREE.LatheGeometry(bowlPoints, 32);
            const bowlMat = new THREE.MeshStandardMaterial({ color: 0xefe8dc, roughness: 0.35 });
            const bowlMesh = new THREE.Mesh(bowlGeo, bowlMat);
            bowlMesh.castShadow = true;
            bowlMesh.receiveShadow = true;
            bowlGroup.add(bowlMesh);

            const honeyGeo = new THREE.CylinderGeometry(0.44, 0.37, 0.16, 28);
            const honeyMat = new THREE.MeshStandardMaterial({
                color: 0xd97b16,
                roughness: 0.12,
                metalness: 0.08
            });
            const honeyMesh = new THREE.Mesh(honeyGeo, honeyMat);
            honeyMesh.position.y = 0.22;
            bowlGroup.add(honeyMesh);

            // 3. Stack of golden crackers on outer left edge (well clear of Brie & Emmentaler)
            const crackerMat = new THREE.MeshStandardMaterial({ color: 0xdca35d, roughness: 0.78 });
            const crackerGeo = new THREE.CylinderGeometry(0.42, 0.42, 0.055, 24);
            const crackerFan = [
                { x: -3.52, y: 0.03, z: -0.42, rx: 0, rz: 0 },
                { x: -3.45, y: 0.09, z: -0.20, rx: 0.12, rz: -0.06 },
                { x: -3.38, y: 0.15, z: 0.02, rx: 0.18, rz: -0.08 },
                { x: -3.30, y: 0.21, z: 0.24, rx: 0.22, rz: -0.1 }
            ];
            for (const c of crackerFan) {
                const cm = new THREE.Mesh(crackerGeo, crackerMat);
                cm.position.set(c.x, c.y, c.z);
                cm.rotation.set(c.rx, 0, c.rz);
                cm.castShadow = true;
                cm.receiveShadow = true;
                boardGroup.add(cm);
            }

            // 4. Walnuts tucked next to bowl/crackers, completely away from cheese mouse lanes
            const walnutMat = new THREE.MeshStandardMaterial({
                color: 0x7b4c29,
                bumpMap: woodBump,
                bumpScale: 0.03,
                roughness: 0.8
            });
            const walnutGeo = new THREE.SphereGeometry(0.15, 14, 12);
            const walnuts = [
                [2.82, 0.10, 0.78, 0.3],
                [2.95, 0.10, -0.72, 1.1],
                [-2.95, 0.10, -1.05, 2.0],
                [2.52, 0.10, 2.58, 0.7]
            ];
            for (const [wx, wy, wz, wr] of walnuts) {
                const w = new THREE.Mesh(walnutGeo, walnutMat);
                w.position.set(wx, wy, wz);
                w.rotation.y = wr;
                w.scale.set(1.22, 0.72, 0.95);
                w.castShadow = true;
                boardGroup.add(w);
            }

            // 5. Artisanal Cheese Knife on outer lower-right rim
            const knifeGroup = new THREE.Group();
            knifeGroup.position.set(2.98, 0.04, -2.08);
            knifeGroup.rotation.y = -0.78;
            boardGroup.add(knifeGroup);

            const handleGeo = new THREE.CapsuleGeometry(0.085, 0.50, 8, 16);
            const handleMat = new THREE.MeshStandardMaterial({ color: 0x4b2a14, roughness: 0.55 });
            const handleMesh = new THREE.Mesh(handleGeo, handleMat);
            handleMesh.rotation.z = Math.PI / 2;
            handleMesh.position.x = -0.44;
            handleMesh.castShadow = true;
            knifeGroup.add(handleMesh);

            const bladeShape = new THREE.Shape();
            bladeShape.moveTo(-0.12, -0.06);
            bladeShape.lineTo(0.64, -0.09);
            bladeShape.quadraticCurveTo(0.82, -0.04, 0.86, 0.08);
            bladeShape.lineTo(-0.12, 0.07);
            bladeShape.closePath();
            const bladeGeo = new THREE.ExtrudeGeometry(bladeShape, { depth: 0.018, bevelEnabled: false });
            const steelMat = new THREE.MeshStandardMaterial({
                color: 0xdce2e8,
                roughness: 0.18,
                metalness: 0.88
            });
            const bladeMesh = new THREE.Mesh(bladeGeo, steelMat);
            bladeMesh.rotation.x = -Math.PI / 2;
            bladeMesh.position.y = 0.015;
            bladeMesh.castShadow = true;
            knifeGroup.add(bladeMesh);
        }
        buildGarnishes();

        // --- Build Cute Animated 3D Mouse ("Het Muisje") ---
        // LocalClipping planes prevent the mouse's rump/tail from ever poking out behind a cheese or hole!
        const localHoleClipPlane = new THREE.Plane(new THREE.Vector3(0, 0, 1), 0.21);
        const localSideClipPlane = new THREE.Plane(new THREE.Vector3(0, 0, 1), 0.07);
        const activeMouseClipPlane = new THREE.Plane(new THREE.Vector3(0, 1, 0), 100);

        function createSpeechSprite() {
            const c = document.createElement('canvas');
            c.width = 256;
            c.height = 128;
            const ctx = c.getContext('2d');

            ctx.fillStyle = 'rgba(255, 250, 238, 0.96)';
            ctx.strokeStyle = '#3b2416';
            ctx.lineWidth = 6;

            // Speech bubble pill + tail
            ctx.beginPath();
            ctx.roundRect(24, 14, 208, 76, 38);
            ctx.fill();
            ctx.stroke();

            ctx.beginPath();
            ctx.moveTo(112, 88);
            ctx.lineTo(128, 114);
            ctx.lineTo(144, 88);
            ctx.closePath();
            ctx.fill();

            ctx.fillStyle = '#2d1a0e';
            ctx.font = 'bold 42px system-ui, sans-serif';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText('Piep! 🧀', 128, 53);

            const tex = new THREE.CanvasTexture(c);
            tex.colorSpace = THREE.SRGBColorSpace;
            const mat = new THREE.SpriteMaterial({ map: tex, transparent: true, opacity: 0, depthWrite: false });
            const sprite = new THREE.Sprite(mat);
            sprite.scale.set(0.55, 0.275, 1);
            sprite.position.set(0, 0.34, 0.12);
            return sprite;
        }

        function buildMouse() {
            // mouseCarrier is attached to boardGroup and moved to whichever hole or side-peek anchor is active
            const mouseCarrier = new THREE.Group();
            mouseCarrier.visible = false;
            boardGroup.add(mouseCarrier);

            // mouseRig moves forward/back inside mouseCarrier and handles body lean/roll
            const mouseRig = new THREE.Group();
            mouseCarrier.add(mouseRig);

            const clipPlanes = [activeMouseClipPlane];

            const furMat = new THREE.MeshStandardMaterial({
                color: 0x8b837c,
                roughness: 0.78,
                clippingPlanes: clipPlanes,
                clipShadows: true
            });
            const bellyMat = new THREE.MeshStandardMaterial({
                color: 0xd9cfc5,
                roughness: 0.82,
                clippingPlanes: clipPlanes
            });
            const pinkMat = new THREE.MeshStandardMaterial({
                color: 0xf29ca8,
                roughness: 0.52,
                clippingPlanes: clipPlanes,
                clipShadows: true
            });
            const eyeMat = new THREE.MeshStandardMaterial({
                color: 0x121010,
                roughness: 0.08,
                metalness: 0.1,
                clippingPlanes: clipPlanes
            });
            const glintMat = new THREE.MeshBasicMaterial({
                color: 0xffffff,
                clippingPlanes: clipPlanes
            });
            const whiskerMat = new THREE.MeshBasicMaterial({
                color: 0xf4eee6,
                clippingPlanes: clipPlanes
            });

            // Plump body (oriented along Z: snout toward +Z, tail toward -Z, back toward +Y)
            const bodyGeo = new THREE.SphereGeometry(0.155, 24, 18);
            const bodyMesh = new THREE.Mesh(bodyGeo, furMat);
            bodyMesh.scale.set(0.92, 0.86, 1.32);
            bodyMesh.position.set(0, 0.13, -0.14);
            bodyMesh.castShadow = true;
            mouseRig.add(bodyMesh);

            const bellyMesh = new THREE.Mesh(new THREE.SphereGeometry(0.125, 18, 14), bellyMat);
            bellyMesh.scale.set(0.82, 0.65, 1.18);
            bellyMesh.position.set(0, 0.085, -0.12);
            mouseRig.add(bellyMesh);

            // Articulated Head Group (pivots at neck!)
            const headGroup = new THREE.Group();
            headGroup.position.set(0, 0.165, 0.02);
            mouseRig.add(headGroup);

            const cranium = new THREE.Mesh(new THREE.SphereGeometry(0.118, 22, 16), furMat);
            cranium.scale.set(0.96, 0.90, 1.12);
            cranium.position.set(0, 0, 0.04);
            cranium.castShadow = true;
            headGroup.add(cranium);

            // Pointed snout cone pointing +Z
            const snoutGeo = new THREE.ConeGeometry(0.088, 0.18, 20);
            const snout = new THREE.Mesh(snoutGeo, furMat);
            snout.rotation.x = Math.PI / 2;
            snout.position.set(0, -0.015, 0.135);
            snout.castShadow = true;
            headGroup.add(snout);

            // Cute twitching noseGroup at tip of snout
            const noseGroup = new THREE.Group();
            noseGroup.position.set(0, -0.012, 0.228);
            headGroup.add(noseGroup);

            const noseBall = new THREE.Mesh(new THREE.SphereGeometry(0.026, 14, 12), pinkMat);
            noseGroup.add(noseBall);

            // Whiskers (3 left, 3 right)
            const whiskerGeo = new THREE.CylinderGeometry(0.0028, 0.0012, 0.15, 5);
            for (const side of [-1, 1]) {
                for (let w = -1; w <= 1; w++) {
                    const wh = new THREE.Mesh(whiskerGeo, whiskerMat);
                    wh.position.set(side * 0.075, w * 0.012, -0.025);
                    wh.rotation.z = side * (Math.PI / 2 + w * 0.18);
                    wh.rotation.y = -side * 0.24;
                    noseGroup.add(wh);
                }
            }

            // Glossy dark eyes + specular catchlights
            for (const side of [-1, 1]) {
                const eye = new THREE.Mesh(new THREE.SphereGeometry(0.024, 14, 12), eyeMat);
                eye.position.set(side * 0.072, 0.028, 0.115);
                headGroup.add(eye);

                const glint = new THREE.Mesh(new THREE.SphereGeometry(0.0075, 8, 8), glintMat);
                glint.position.set(side * 0.078, 0.036, 0.134);
                headGroup.add(glint);
            }

            // Big round mouse ears (left & right groups so they twitch!)
            function makeEar(side) {
                const earPivot = new THREE.Group();
                earPivot.position.set(side * 0.088, 0.082, 0.01);
                earPivot.rotation.set(-0.2, side * 0.32, -side * 0.28);

                const outerEar = new THREE.Mesh(new THREE.CylinderGeometry(0.075, 0.075, 0.018, 20), furMat);
                outerEar.rotation.x = Math.PI / 2;
                outerEar.position.y = 0.04;
                outerEar.castShadow = true;
                earPivot.add(outerEar);

                const innerEar = new THREE.Mesh(new THREE.CylinderGeometry(0.052, 0.052, 0.02, 18), pinkMat);
                innerEar.rotation.x = Math.PI / 2;
                innerEar.position.set(0, 0.04, 0.004);
                earPivot.add(innerEar);

                headGroup.add(earPivot);
                return earPivot;
            }
            const leftEar = makeEar(-1);
            const rightEar = makeEar(1);

            // Cute front paws
            const pawGeo = new THREE.SphereGeometry(0.036, 14, 10);
            const leftPaw = new THREE.Mesh(pawGeo, pinkMat);
            leftPaw.scale.set(0.9, 0.55, 1.35);
            leftPaw.position.set(-0.072, 0.035, 0.09);
            mouseRig.add(leftPaw);

            const rightPaw = new THREE.Mesh(pawGeo, pinkMat);
            rightPaw.scale.set(0.9, 0.55, 1.35);
            rightPaw.position.set(0.072, 0.035, 0.09);
            mouseRig.add(rightPaw);

            // Little hind feet (visible when peeking alongside a cheese!)
            for (const side of [-1, 1]) {
                const foot = new THREE.Mesh(pawGeo, pinkMat);
                foot.scale.set(0.95, 0.5, 1.55);
                foot.position.set(side * 0.095, 0.024, -0.20);
                mouseRig.add(foot);
            }

            // Wagging pink tail
            const tailPivot = new THREE.Group();
            tailPivot.position.set(0, 0.08, -0.32);
            mouseRig.add(tailPivot);

            const tailCurve = new THREE.CatmullRomCurve3([
                new THREE.Vector3(0, 0, 0),
                new THREE.Vector3(0.02, -0.03, -0.14),
                new THREE.Vector3(-0.04, 0.01, -0.28),
                new THREE.Vector3(0.03, 0.05, -0.38)
            ]);
            const tailGeo = new THREE.TubeGeometry(tailCurve, 18, 0.015, 8, false);
            const tailMesh = new THREE.Mesh(tailGeo, pinkMat);
            tailPivot.add(tailMesh);

            const speechSprite = createSpeechSprite();
            headGroup.add(speechSprite);

            return {
                carrier: mouseCarrier,
                rig: mouseRig,
                headGroup,
                noseGroup,
                leftEar,
                rightEar,
                leftPaw,
                rightPaw,
                tailPivot,
                speechSprite
            };
        }

        const mouse = buildMouse();

        // --- Mouse Animation State Machine ---
        const mouseState = {
            active: false,
            mode: 'hole', // 'hole' | 'side'
            elapsed: 0,
            duration: 3.9,
            spot: null,
            cheese: null,
            nextAutoTime: 2.2, // First visit happens shortly after opening the page!
            lastCheeseName: '',
            preferHolesNext: true
        };

        let toastTimer = null;
        function showMouseToast(text) {
            mouseToast.textContent = text;
            mouseToast.classList.add('visible');
            if (toastTimer) clearTimeout(toastTimer);
            toastTimer = setTimeout(() => {
                mouseToast.classList.remove('visible');
            }, 3400);
        }

        const _worldPosA = new THREE.Vector3();
        const _worldPosB = new THREE.Vector3();
        const _worldQuatA = new THREE.Quaternion();
        const _boardInvMat = new THREE.Matrix4();
        const _rayDir = new THREE.Vector3();
        const _spotNormal = new THREE.Vector3();
        const _occlusionRay = new THREE.Raycaster();

        function attachCarrierToBoardSpaceOf(targetObj) {
            boardGroup.updateMatrixWorld(true);
            _boardInvMat.copy(boardGroup.matrixWorld).invert();
            const relMat = new THREE.Matrix4().multiplyMatrices(_boardInvMat, targetObj.matrixWorld);
            relMat.decompose(mouse.carrier.position, mouse.carrier.quaternion, mouse.carrier.scale);
            mouse.carrier.scale.set(1, 1, 1);
        }

        function isPointOccludedFromCamera(worldTarget, margin = 0.14) {
            _rayDir.subVectors(worldTarget, camera.position);
            const totalDist = _rayDir.length();
            if (totalDist < 0.01) return false;
            _rayDir.divideScalar(totalDist);
            _occlusionRay.set(camera.position, _rayDir);
            _occlusionRay.far = Math.max(0.05, totalDist - margin);
            const hits = _occlusionRay.intersectObjects(cheeseOccluderMeshes, false);
            return hits.length > 0;
        }

        function pickBestHoleSpot(cheese) {
            boardGroup.updateMatrixWorld(true);
            let bestSpot = cheese.holeSpots[0];
            let bestScore = -Infinity;

            for (const spot of cheese.holeSpots) {
                spot.anchor.getWorldPosition(_worldPosA);
                spot.anchor.getWorldQuaternion(_worldQuatA);
                // Anchor +Z is the outward normal of the cheese hole
                _spotNormal.set(0, 0, 1).applyQuaternion(_worldQuatA).normalize();

                const toCam = _rayDir.subVectors(camera.position, _worldPosA).normalize();
                const facing = _spotNormal.dot(toCam);

                // Check whether the mouth of the hole is visible (not blocked by a foreground cheese)
                const mouthProbe = _worldPosB.copy(_worldPosA).addScaledVector(_spotNormal, 0.18);
                const blocked = isPointOccludedFromCamera(mouthProbe, 0.12);

                let score = (Math.random() - 0.5) * 8;
                if (!blocked) score += 60;
                // Sweet spot: hole angled toward camera (0.15..0.82), not facing away and not staring straight down barrel
                if (facing >= 0.15 && facing <= 0.82) score += 45;
                else if (facing > 0.82) score += 18;
                else score -= 50;

                if (score > bestScore) {
                    bestScore = score;
                    bestSpot = spot;
                }
            }
            return bestSpot;
        }

        function scoreSidePeekSpot(spot) {
            spot.peekAnchor.getWorldPosition(_worldPosA);
            spot.peekAnchor.getWorldQuaternion(_worldQuatA);
            spot.behindProbe.getWorldPosition(_worldPosB);

            const peekFwd = _spotNormal.set(0, 0, 1).applyQuaternion(_worldQuatA).normalize();
            const toCamHoriz = new THREE.Vector3(
                camera.position.x - _worldPosA.x,
                0,
                camera.position.z - _worldPosA.z
            );
            if (toCamHoriz.lengthSq() > 1e-4) toCamHoriz.normalize();
            const fwdDot = peekFwd.dot(toCamHoriz);

            // Ideal side-peek:
            // 1) behindProbe IS occluded behind the cheese from camera POV (so you NEVER see the mouse spawn!)
            // 2) peeked snout position IS visible around the silhouette edge of the cheese!
            // 3) peekFwd faces generally toward/across camera view (fwdDot > 0.1) so clip back is never visible!
            const hideOccluded = isPointOccludedFromCamera(_worldPosB, 0.10);
            const snoutWorld = _worldPosA.clone().addScaledVector(peekFwd, 0.22);
            snoutWorld.y += 0.18;
            const snoutBlocked = isPointOccludedFromCamera(snoutWorld, 0.10);

            let score = (Math.random() - 0.5) * 6;
            if (hideOccluded) score += 85;
            if (!snoutBlocked) score += 50;
            if (fwdDot > 0.18) score += 45 * fwdDot;
            else if (fwdDot < -0.05) score -= 75;

            return score;
        }

        function triggerMouseVisit(specificCheese = null) {
            if (mouseState.active) return;

            boardGroup.updateMatrixWorld(true);

            let chosenCheese = specificCheese;
            let chosenSideSpot = null;

            if (!chosenCheese) {
                // Sort all 6 cheeses by distance to the camera POV and EXCLUDE the closest ones
                // so the mouse NEVER spawns right in front of the viewer's nose!
                const ranked = cheeses.map(c => {
                    c.group.getWorldPosition(_worldPosA);
                    return { cheese: c, camDist: camera.position.distanceTo(_worldPosA) };
                }).sort((a, b) => a.camDist - b.camDist);

                // Keep only middle/far cheeses (skip the 2 closest to POV; for the 2 holey cheeses, pick the further one!)
                const notClosestSet = new Set(ranked.slice(2).map(r => r.cheese));

                if (mouseState.preferHolesNext) {
                    mouseState.preferHolesNext = false;
                    const holey = cheeses.filter(c => c.hasHoles);
                    // Pick the holey cheese that is further from the camera POV (or not closest)
                    holey.sort((a, b) => {
                        const da = camera.position.distanceTo(a.group.getWorldPosition(new THREE.Vector3()));
                        const db = camera.position.distanceTo(b.group.getWorldPosition(new THREE.Vector3()));
                        return db - da;
                    });
                    chosenCheese = holey[0];
                } else {
                    mouseState.preferHolesNext = true;
                    const solidCandidates = cheeses.filter(c => !c.hasHoles && notClosestSet.has(c));
                    const pool = solidCandidates.length > 0 ? solidCandidates : cheeses.filter(c => !c.hasHoles);

                    // Score every side-peek spot across eligible middle/far cheeses to find one genuinely hidden behind a cheese!
                    let bestScore = -Infinity;
                    for (const c of pool) {
                        const repeatPenalty = c.name === mouseState.lastCheeseName ? 22 : 0;
                        for (const sp of c.sidePeekSpots) {
                            const sc = scoreSidePeekSpot(sp) - repeatPenalty;
                            if (sc > bestScore) {
                                bestScore = sc;
                                chosenCheese = c;
                                chosenSideSpot = sp;
                            }
                        }
                    }
                }
            }

            mouseState.lastCheeseName = chosenCheese.name;
            mouseState.cheese = chosenCheese;
            mouseState.elapsed = 0;
            mouseState.active = true;
            mouse.carrier.visible = true;
            mouse.speechSprite.material.opacity = 0;

            if (chosenCheese.hasHoles && chosenCheese.holeSpots.length > 0) {
                // CASE 1: Cheese HAS holes -> Mouse emerges right out of a shadowed cheese hole!
                mouseState.mode = 'hole';
                mouseState.duration = 3.85;
                const spot = pickBestHoleSpot(chosenCheese);
                mouseState.spot = spot;

                attachCarrierToBoardSpaceOf(spot.anchor);
                mouse.rig.scale.setScalar((spot.radius / 0.23) * 0.92);
                mouse.rig.position.set(0, -0.155, -0.45);
                mouse.rig.rotation.set(0, 0, 0);
                boardGroup.updateMatrixWorld(true);
                activeMouseClipPlane.copy(localHoleClipPlane).applyMatrix4(mouse.carrier.matrixWorld);

                showMouseToast(`🐭 Muisje komt uit een gat van de ${chosenCheese.name}!`);
            } else {
                // CASE 2: Cheese has NO holes -> Mouse peeks from behind the cheese edge!
                mouseState.mode = 'side';
                mouseState.duration = 3.90;

                if (!chosenSideSpot) {
                    let bestSc = -Infinity;
                    for (const sp of chosenCheese.sidePeekSpots) {
                        const sc = scoreSidePeekSpot(sp);
                        if (sc > bestSc) {
                            bestSc = sc;
                            chosenSideSpot = sp;
                        }
                    }
                }
                mouseState.spot = chosenSideSpot;

                attachCarrierToBoardSpaceOf(chosenSideSpot.peekAnchor);
                mouse.rig.scale.setScalar(0.92);
                mouse.rig.position.set(chosenSideSpot.cheeseSide * 0.18, 0, -0.40);
                mouse.rig.rotation.set(0, 0, 0);
                boardGroup.updateMatrixWorld(true);
                activeMouseClipPlane.copy(localSideClipPlane).applyMatrix4(mouse.carrier.matrixWorld);

                showMouseToast(`🐭 Geen gaten in de ${chosenCheese.name} — muisje piept langs het kaasje!`);
            }
        }

        function easeOutBack(x) {
            const c1 = 1.70158;
            const c3 = c1 + 1;
            return 1 + c3 * Math.pow(x - 1, 3) + c1 * Math.pow(x - 1, 2);
        }

        function easeInOutCubic(x) {
            return x < 0.5 ? 4 * x * x * x : 1 - Math.pow(-2 * x + 2, 3) / 2;
        }

        function updateMouseAnimation(dt, totalTime) {
            if (!mouseState.active) {
                mouseState.nextAutoTime -= dt;
                if (mouseState.nextAutoTime <= 0) {
                    triggerMouseVisit(null);
                }
                return;
            }

            mouseState.elapsed += dt;
            const t = mouseState.elapsed;
            const dur = mouseState.duration;

            if (t >= dur) {
                mouseState.active = false;
                mouse.carrier.visible = false;
                mouse.speechSprite.material.opacity = 0;
                // Schedule next "af en toe" mouse event in 4.2 - 7.8 seconds
                mouseState.nextAutoTime = 4.2 + Math.random() * 3.6;
                return;
            }

            // Expressive continuous micro-animations (whisker twitch, ear flutter, tail wag)
            const whiskerFlutter = Math.sin(totalTime * 28) * 0.08;
            mouse.noseGroup.rotation.z = whiskerFlutter;
            mouse.noseGroup.scale.setScalar(1 + Math.sin(totalTime * 20) * 0.08);
            mouse.leftEar.rotation.z = 0.28 + Math.sin(totalTime * 11) * 0.09;
            mouse.rightEar.rotation.z = -0.28 - Math.sin(totalTime * 11 + 1.1) * 0.09;
            mouse.tailPivot.rotation.y = Math.sin(totalTime * 9) * 0.35;

            // Speech bubble "Piep!" window in the middle of the peek
            let bubbleAlpha = 0;
            if (t > 1.35 && t < 2.55) {
                bubbleAlpha = Math.sin(((t - 1.35) / 1.2) * Math.PI);
            }
            mouse.speechSprite.material.opacity = Math.max(0, Math.min(1, bubbleAlpha));

            if (mouseState.mode === 'hole') {
                const isTop = mouseState.spot.isTopHole;
                // Timeline:
                // 0.00 - 0.55: cautious snout poke
                // 0.55 - 1.15: full pop out of hole
                // 1.15 - 3.05: look around left & right, paws on rim
                // 3.05 - 3.85: retreat smoothly into cheese hole
                let zSlide;
                if (t < 0.55) {
                    const p = easeInOutCubic(t / 0.55);
                    zSlide = THREE.MathUtils.lerp(-0.42, -0.17, p);
                } else if (t < 1.15) {
                    const p = easeOutBack((t - 0.55) / 0.60);
                    zSlide = THREE.MathUtils.lerp(-0.17, 0.11, p);
                } else if (t < 3.05) {
                    zSlide = 0.11 + Math.sin((t - 1.15) * 3.2) * 0.015;
                } else {
                    const p = easeInOutCubic((t - 3.05) / (dur - 3.05));
                    zSlide = THREE.MathUtils.lerp(0.11, -0.44, p);
                }

                mouse.rig.position.set(0, -0.155, zSlide);

                // Head look-around
                const lookPhase = Math.max(0, t - 0.9);
                const headYaw = Math.sin(lookPhase * 3.1) * 0.48;
                // When popping out of a top hole, tilt head forward so the mouse looks across the plank!
                const basePitch = isTop ? 0.85 : -0.08;
                const headPitch = basePitch + Math.sin(lookPhase * 5.2) * 0.1;
                mouse.headGroup.rotation.set(headPitch, headYaw, Math.sin(lookPhase * 2.5) * 0.12);

                // Little front paws resting/waving on hole rim
                mouse.leftPaw.position.set(-0.075, 0.045, 0.11);
                mouse.rightPaw.position.set(0.075, 0.045 + Math.max(0, Math.sin(lookPhase * 6)) * 0.03, 0.11);

                // Keep clipping plane locked to the cheese hole in world space
                boardGroup.updateMatrixWorld(true);
                activeMouseClipPlane.copy(localHoleClipPlane).applyMatrix4(mouse.carrier.matrixWorld);
            } else {
                // Mode === 'side': curve nose-first out from behind the occluded edge of a cheese!
                const cheeseSide = mouseState.spot.cheeseSide;
                let progress;

                if (t < 0.72) {
                    progress = easeOutBack(Math.min(1, t / 0.72));
                } else if (t < 3.10) {
                    progress = 1;
                } else {
                    const p = easeInOutCubic((t - 3.10) / (dur - 3.10));
                    progress = 1 - p;
                }

                // Tucked behind cheese at progress=0 (+cheeseSide * 0.18 behind cheese wall & -0.40 behind clip plane),
                // curving out around corner at progress=1 (x=0, z=+0.07)
                const clampedP = Math.min(1, Math.max(0, progress));
                const xSlide = THREE.MathUtils.lerp(cheeseSide * 0.18, 0.0, clampedP);
                const zSlide = THREE.MathUtils.lerp(-0.40, 0.07, progress);
                const hopY = Math.abs(Math.sin(t * 13)) * (clampedP > 0.08 && clampedP < 0.94 ? 0.022 : 0);

                mouse.rig.position.set(xSlide, hopY, zSlide);

                // Lean head AWAY from cheeseSide around the silhouette edge of the cheese!
                mouse.rig.rotation.set(0, -cheeseSide * 0.12 * clampedP, cheeseSide * 0.24 * clampedP);

                const lookPhase = Math.max(0, t - 0.72);
                mouse.headGroup.rotation.set(
                    -0.12 + Math.sin(lookPhase * 4.5) * 0.09,
                    -cheeseSide * 0.22 + Math.sin(lookPhase * 3.2) * 0.42,
                    cheeseSide * 0.14
                );
                mouse.leftPaw.position.set(-0.072, 0.035, 0.09);
                mouse.rightPaw.position.set(0.072, 0.035, 0.09);

                // Keep corner clipping plane locked to the cheese edge in world space
                boardGroup.updateMatrixWorld(true);
                activeMouseClipPlane.copy(localSideClipPlane).applyMatrix4(mouse.carrier.matrixWorld);
            }
        }

        // --- Idle Rotation & Interactive Perspective Control ---
        let idleEnabled = true;
        let isUserDragging = false;
        let lastInteractionTime = -10;
        const IDLE_RESUME_DELAY = 2.6; // seconds after user stops moving before smooth idle spin resumes
        let currentSpinSpeed = 0.30;   // rad/s
        const TARGET_SPIN_SPEED = 0.30;

        // Smooth camera tween for preset buttons / slider
        let cameraTween = null;

        function setPerspectivePolar(targetPolar, smooth = true) {
            const clamped = THREE.MathUtils.clamp(targetPolar, POLAR_TOP, POLAR_FRONT);
            const offset = camera.position.clone().sub(controls.target);
            const spherical = new THREE.Spherical().setFromVector3(offset);

            if (!smooth) {
                spherical.phi = clamped;
                offset.setFromSpherical(spherical);
                camera.position.copy(controls.target).add(offset);
                controls.update();
                syncPerspectiveUI();
                return;
            }

            cameraTween = {
                startPhi: spherical.phi,
                endPhi: clamped,
                startRadius: spherical.radius,
                endRadius: THREE.MathUtils.clamp(spherical.radius, 9.2, 12.2),
                elapsed: 0,
                duration: 0.65
            };
        }

        function syncPerspectiveUI() {
            const polar = controls.getPolarAngle();
            // slider: 0 = Vooraanzicht (POLAR_FRONT), 100 = Bovenaanzicht (POLAR_TOP)
            const t = (POLAR_FRONT - polar) / (POLAR_FRONT - POLAR_TOP);
            const sliderVal = Math.round(THREE.MathUtils.clamp(t * 100, 0, 100));
            angleSlider.value = String(sliderVal);

            // Elevation angle in degrees above board horizon (0° = vooraanzicht, 90° = bovenaanzicht)
            const elevDeg = Math.round(THREE.MathUtils.radToDeg(Math.PI / 2 - polar));
            angleReadout.textContent = `${elevDeg}°`;

            // Highlight matching preset button if close
            viewButtons.forEach(btn => {
                const mode = btn.dataset.view;
                let isMatch = false;
                if (mode === 'front' && elevDeg <= 14) isMatch = true;
                else if (mode === 'top' && elevDeg >= 78) isMatch = true;
                else if (mode === 'angled' && elevDeg > 14 && elevDeg < 78) isMatch = true;
                btn.classList.toggle('active', isMatch);
            });
        }

        function updateStatusIndicator(nowSec) {
            if (!idleEnabled) {
                statusDot.className = 'status-dot paused';
                statusText.textContent = 'Idle draaien gepauzeerd';
            } else if (isUserDragging || (nowSec - lastInteractionTime) < IDLE_RESUME_DELAY) {
                statusDot.className = 'status-dot manual';
                statusText.textContent = 'Handmatig bekijken (pauzeert even)';
            } else {
                statusDot.className = 'status-dot';
                statusText.textContent = 'Draait automatisch (idle)';
            }
        }

        controls.addEventListener('start', () => {
            isUserDragging = true;
            cameraTween = null;
            lastInteractionTime = performance.now() / 1000;
        });

        controls.addEventListener('change', () => {
            if (isUserDragging) {
                lastInteractionTime = performance.now() / 1000;
            }
            syncPerspectiveUI();
        });

        controls.addEventListener('end', () => {
            isUserDragging = false;
            lastInteractionTime = performance.now() / 1000;
        });

        // View preset buttons
        viewButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                lastInteractionTime = performance.now() / 1000 - (IDLE_RESUME_DELAY - 1.0);
                const view = btn.dataset.view;
                if (view === 'front') setPerspectivePolar(POLAR_FRONT, true);
                else if (view === 'top') setPerspectivePolar(POLAR_TOP, true);
                else setPerspectivePolar(POLAR_ANGLED, true);
            });
        });

        // Continuous slider between Vooraanzicht (0) and Bovenaanzicht (100)
        angleSlider.addEventListener('input', () => {
            cameraTween = null;
            lastInteractionTime = performance.now() / 1000 - (IDLE_RESUME_DELAY - 1.2);
            const t = Number(angleSlider.value) / 100;
            const targetPolar = THREE.MathUtils.lerp(POLAR_FRONT, POLAR_TOP, t);
            setPerspectivePolar(targetPolar, false);
        });

        // Toggle idle rotation
        toggleIdleBtn.addEventListener('click', () => {
            idleEnabled = !idleEnabled;
            toggleIdleBtn.classList.toggle('active', idleEnabled);
            toggleIdleBtn.textContent = idleEnabled ? '⟳ Idle draaien: Aan' : '⏸ Idle draaien: Uit';
        });

        // Summon mouse button
        summonMouseBtn.addEventListener('click', () => {
            if (mouseState.active) {
                // Allow immediate re-summon if user clicks button excitedly
                mouseState.active = false;
                mouse.carrier.visible = false;
            }
            triggerMouseVisit(null);
        });

        // --- Raycasting: Hover Tooltip & Click Cheese to Lure Mouse ---
        const raycaster = new THREE.Raycaster();
        const pointer = new THREE.Vector2(-99, -99);
        let pointerDownPos = { x: 0, y: 0 };

        renderer.domElement.addEventListener('pointermove', (e) => {
            pointer.x = (e.clientX / window.innerWidth) * 2 - 1;
            pointer.y = -(e.clientY / window.innerHeight) * 2 + 1;

            if (isUserDragging) {
                cheeseTooltip.classList.remove('visible');
                return;
            }

            raycaster.setFromCamera(pointer, camera);
            const hits = raycaster.intersectObjects(interactiveCheeseMeshes, false);
            if (hits.length > 0 && hits[0].object.userData.cheese) {
                const c = hits[0].object.userData.cheese;
                cheeseTooltip.innerHTML = `${c.name}<small>${c.subtitle}</small>`;
                cheeseTooltip.style.left = `${e.clientX}px`;
                cheeseTooltip.style.top = `${e.clientY}px`;
                cheeseTooltip.classList.add('visible');
            } else {
                cheeseTooltip.classList.remove('visible');
            }
        });

        renderer.domElement.addEventListener('pointerdown', (e) => {
            pointerDownPos = { x: e.clientX, y: e.clientY };
        });

        renderer.domElement.addEventListener('pointerup', (e) => {
            const dist = Math.hypot(e.clientX - pointerDownPos.x, e.clientY - pointerDownPos.y);
            if (dist > 6) return; // Was a drag, not a click

            pointer.x = (e.clientX / window.innerWidth) * 2 - 1;
            pointer.y = -(e.clientY / window.innerHeight) * 2 + 1;
            raycaster.setFromCamera(pointer, camera);
            const hits = raycaster.intersectObjects(interactiveCheeseMeshes, false);
            if (hits.length > 0 && hits[0].object.userData.cheese) {
                const clickedCheese = hits[0].object.userData.cheese;
                if (mouseState.active) {
                    mouseState.active = false;
                    mouse.carrier.visible = false;
                }
                triggerMouseVisit(clickedCheese);
            }
        });

        window.addEventListener('resize', () => {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        });

        // Initial UI sync
        syncPerspectiveUI();

        // --- Main Animation Loop ---
        const clock = new THREE.Clock();

        function animate() {
            requestAnimationFrame(animate);

            const dt = Math.min(clock.getDelta(), 0.05);
            const elapsed = clock.elapsedTime;
            const nowSec = performance.now() / 1000;

            // Smooth camera preset tween
            if (cameraTween) {
                cameraTween.elapsed += dt;
                const p = Math.min(1, cameraTween.elapsed / cameraTween.duration);
                const eased = easeInOutCubic(p);

                const offset = camera.position.clone().sub(controls.target);
                const sph = new THREE.Spherical().setFromVector3(offset);
                sph.phi = THREE.MathUtils.lerp(cameraTween.startPhi, cameraTween.endPhi, eased);
                sph.radius = THREE.MathUtils.lerp(cameraTween.startRadius, cameraTween.endRadius, eased);
                offset.setFromSpherical(sph);
                camera.position.copy(controls.target).add(offset);

                if (p >= 1) cameraTween = null;
                syncPerspectiveUI();
            }

            // Smooth idle rotation of the kaasplank
            const isIdle = idleEnabled && !isUserDragging && (nowSec - lastInteractionTime) >= IDLE_RESUME_DELAY;
            const desiredSpeed = isIdle ? TARGET_SPIN_SPEED : 0;
            currentSpinSpeed = THREE.MathUtils.lerp(currentSpinSpeed, desiredSpeed, Math.min(1, dt * 3.5));
            boardGroup.rotation.y += currentSpinSpeed * dt;

            updateStatusIndicator(nowSec);
            updateMouseAnimation(dt, elapsed);

            controls.update();
            renderer.render(scene, camera);
        }

        animate();
    </script>
</body>
</html>
