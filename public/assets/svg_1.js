function prepareSiteplanTheme(svgElement) {
    function neutral(value) {
        const parts = value.match(/[\d.]+/g);
        if (!parts || parts.length < 3 || (parts.length > 3 && Number(parts[3]) === 0)) return null;
        const rgb = parts.slice(0,3).map(Number);
        if (Math.max(...rgb) - Math.min(...rgb) > 15) return null;
        return rgb[0] >= 230 ? 'paper' : rgb[0] <= 70 ? 'ink' : 'shade';
    }
    svgElement.querySelectorAll('path,polygon,rect,polyline,line,circle,ellipse,text,tspan').forEach(function(element) {
        if (element.closest('defs,.detail-button')) return;
        const style = getComputedStyle(element);
        const fill = neutral(style.fill), stroke = neutral(style.stroke);
        if (fill) element.dataset.planFill = fill;
        if (stroke) element.dataset.planStroke = stroke;
    });
    svgElement.querySelectorAll('.detail-button').forEach(function(link) {
        const shape = link.querySelector('polygon,path');
        const values = shape && getComputedStyle(shape).fill.match(/[\d.]+/g);
        const light = values && (.2126*Number(values[0])+.7152*Number(values[1])+.0722*Number(values[2])) >= 160;
        link.dataset.label = light ? 'dark' : 'light';
        link.addEventListener('keydown', function(event) {
            if(event.key==='Enter' || event.key===' ') { event.preventDefault(); link.dispatchEvent(new MouseEvent('click',{bubbles:true})); }
        });
    });
}

function initializeSVGControls(svgElement, svgContainer, resetButton) {
    if (svgElement.dataset.controlsReady) return;
    svgElement.dataset.controlsReady = 'true';
    prepareSiteplanTheme(svgElement);
    let scale=1, translateX=0, translateY=0, pointer=null, startX=0, startY=0, originalX=0, originalY=0, dragged=false, suppressClick=false;
    const panel=svgContainer.closest('.siteplan-loaded');
    const label=panel?.querySelector('.siteplan-zoom-level');
    function applyTransform() {
        svgElement.style.transform=`translate(${translateX}px, ${translateY}px) scale(${scale})`;
        if(label) label.textContent=Math.round(scale*100)+'%';
    }
    function zoom(next, x=svgContainer.clientWidth/2, y=svgContainer.clientHeight/2) {
        next=Math.max(1,Math.min(6,next));
        const localX=(x-translateX)/scale, localY=(y-translateY)/scale;
        translateX=x-localX*next;translateY=y-localY*next;scale=next;
        if(scale===1) translateX=translateY=0;
        applyTransform();
    }
    svgContainer.addEventListener('wheel',function(event) {
        event.preventDefault();
        const box=svgContainer.getBoundingClientRect();
        zoom(scale*(event.deltaY<0?1.12:1/1.12),event.clientX-box.left,event.clientY-box.top);
    },{passive:false});
    panel?.querySelector('[data-zoom="in"]')?.addEventListener('click',()=>zoom(scale*1.25));
    panel?.querySelector('[data-zoom="out"]')?.addEventListener('click',()=>zoom(scale/1.25));
    resetButton.addEventListener('click',function(){scale=1;translateX=translateY=0;applyTransform();});
    svgContainer.addEventListener('pointerdown',function(event) {
        if(pointer!==null || event.button!==0) return;
        pointer=event.pointerId;startX=event.clientX;startY=event.clientY;originalX=translateX;originalY=translateY;dragged=false;
    });
    window.addEventListener('pointermove',function(event) {
        if(event.pointerId!==pointer) return;
        const dx=event.clientX-startX,dy=event.clientY-startY;
        if(Math.hypot(dx,dy)>5) dragged=true;
        if(dragged) {translateX=originalX+dx;translateY=originalY+dy;svgElement.style.cursor='grabbing';applyTransform();}
    });
    function endDrag(event) {
        if(event.pointerId!==pointer) return;
        pointer=null;svgElement.style.cursor='grab';
        if(dragged) {suppressClick=true;setTimeout(()=>suppressClick=false,250);}
    }
    window.addEventListener('pointerup',endDrag);
    window.addEventListener('pointercancel',endDrag);
    svgContainer.addEventListener('click',function(event){if(suppressClick){event.preventDefault();event.stopImmediatePropagation();}},{capture:true});
    applyTransform();
}