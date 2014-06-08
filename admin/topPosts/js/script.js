createBarGraph('#postConversion', conversionData);

function createBarGraph(element, dataset) {
    console.log ("dataset: " + dataset);
    // Settings for D3.js Graphs
    var margin = {
        top: 20,
        right: 20,
        bottom: 150,
        left: 40
    },
        width = 800 - margin.left - margin.right,
        height = 400 - margin.top - margin.bottom;

    
    var yLabel = 'Rate';
    var yFormat = d3.format(".0%");
    
    // Parse the date / time
    var x = d3.scale.ordinal().rangeRoundBands([0, width], .05);
    var y = d3.scale.linear().range([height, 0]);
    var xAxis = d3.svg.axis().scale(x).orient("bottom");
    var yAxis = d3.svg.axis().scale(y).orient("left").tickFormat(yFormat).ticks(5);
    // Add a little tooltip
    var tip = d3.tip().attr('class', 'd3-tip').offset([-10, 0]).html(function(d) {
        return "Value: <span style='color:red'>" + (Math.round(d.rate * 1000) / 10) + "%</span>";
    })
    var svg = d3.select(element).append("svg").attr("width", width + margin.left + margin.right).attr("height", height + margin.top + margin.bottom).append("g").attr("transform", "translate(" + margin.left + "," + margin.top + ")");
    // Add the tooltip
    svg.call(tip);
    dataset.forEach(function(d) {
        d.rate = +d.rate;
    });
    x.domain(dataset.map(function(d) {
        return d.title;
    }));
    y.domain([0, d3.max(dataset, function(d) {
        return d.rate;
    })]);
    svg.append("g").attr("class", "x axis").attr("transform", "translate(0," + height + ")").call(xAxis).selectAll("text").style("text-anchor", "end").attr("dx", "-.8em").attr("dy", "-.55em").attr("transform", "rotate(-65)");
    svg.append("g").attr("class", "y axis").call(yAxis).append("text").attr("transform", "rotate(-90)").attr("y", 6).attr("dy", ".71em").style("text-anchor", "end").text(yLabel);
    svg.selectAll("bar").data(dataset).enter().append("rect").attr("class", "ois-stats-bar").attr("x", function(d) {
        return x(d.title) + 15;
    }).on('mouseover', tip.show).on('mouseout', tip.hide).attr("width", x.rangeBand()).attr("y", function(d) {
        return y(d.rate);
    }).attr("height", function(d) {
        return height - y(d.rate);
    });
}