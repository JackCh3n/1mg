/**
 * 仪表盘:活跃日历(GitHub contributions 风格)
 * 数据来自 tpl_index.php 输出的 #calendar 的 data-json
 */
(function () {
    var box = document.getElementById('calendar');
    if (!box) return;

    var data = [];
    try {
        data = JSON.parse(box.getAttribute('data-json') || '[]');
    } catch (e) { return; }
    if (!data.length) return;

    var CELL = 11, GAP = 3;

    //星期(周一为0)
    function weekday(d) {
        return (new Date(d + 'T00:00:00').getDay() + 6) % 7;
    }
    function monthLabel(d) {
        return parseInt(d.slice(5, 7), 10) + '月';
    }

    var cols = [];   //每一列是一周: [null|{d,c,lv} x7]
    var col = [];
    //第一列按第一天的星期留空
    for (var i = 0; i < weekday(data[0].d); i++) col.push(null);
    for (var j = 0; j < data.length; j++) {
        col.push(data[j]);
        if (col.length === 7) { cols.push(col); col = []; }
    }
    if (col.length) { while (col.length < 7) col.push(null); cols.push(col); }

    //月标签行
    var months = [];
    var lastMonth = '';
    cols.forEach(function (c, idx) {
        for (var k = 0; k < 7; k++) {
            if (c[k]) {
                var m = monthLabel(c[k].d);
                if (m !== lastMonth) { months[idx] = m; lastMonth = m; }
                break;
            }
        }
    });

    //构建DOM
    var wrap = document.createElement('div');
    wrap.className = 'cal-wrap';

    var scroll = document.createElement('div');
    scroll.className = 'cal-scroll';

    var grid = document.createElement('div');
    grid.className = 'cal-grid';
    grid.style.gridTemplateColumns = 'repeat(' + cols.length + ', ' + CELL + 'px)';

    var colFrag = document.createDocumentFragment();
    cols.forEach(function (c, ci) {
        var colEl = document.createElement('div');
        colEl.className = 'cal-col';
        for (var k = 0; k < 7; k++) {
            var cell = document.createElement('div');
            cell.className = 'cal-cell' + (c[k] ? ' cal-lv' + c[k].lv : ' cal-empty');
            if (c[k]) {
                cell.title = c[k].d + ' 上传 ' + c[k].c + ' 张';
                if (c[k].d === data[data.length - 1].d) cell.classList.add('cal-today');
            }
            colEl.appendChild(cell);
        }
        colFrag.appendChild(colEl);
    });
    grid.appendChild(colFrag);
    scroll.appendChild(grid);
    wrap.appendChild(scroll);

    //左侧星期标签(一/三/五)
    var dow = document.createElement('div');
    dow.className = 'cal-dow';
    ['一', '', '三', '', '五', '', ''].forEach(function (t) {
        var s = document.createElement('span');
        s.textContent = t;
        dow.appendChild(s);
    });

    //顶部月标签
    var monthRow = document.createElement('div');
    monthRow.className = 'cal-months';
    for (var mi = 0; mi < cols.length; mi++) {
        var s = document.createElement('span');
        s.textContent = months[mi] || '';
        s.style.width = (CELL + GAP) + 'px';
        monthRow.appendChild(s);
    }

    //底部图例
    var legend = document.createElement('div');
    legend.className = 'cal-legend';
    legend.innerHTML = '<span>少</span>' +
        '<i class="cal-cell cal-lv0"></i><i class="cal-cell cal-lv1"></i><i class="cal-cell cal-lv2"></i>' +
        '<i class="cal-cell cal-lv3"></i><i class="cal-cell cal-lv4"></i>' +
        '<span>多</span>';

    var top = document.createElement('div');
    top.className = 'cal-top';
    top.appendChild(monthRow);

    var body = document.createElement('div');
    body.className = 'cal-body';
    body.appendChild(dow);
    body.appendChild(scroll);

    wrap.appendChild(top);
    wrap.appendChild(body);
    wrap.appendChild(legend);
    box.appendChild(wrap);
})();
