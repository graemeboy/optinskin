jQuery(document).ready(function($) {
    // Hide the loading message.
    $('#ois-stats-loading-message').hide();
    // Initially, show the impression statistics.
    $('#impressionStats').show();
    // Create some action listeners.
    var control;
    $('.ois-stats-controller').click(function(e) {
        e.preventDefault();
        // Hide any visible graphs
        $('.ois-stats-graph').hide();
        $('.ois-stats-controller').removeClass('ois-stats-active');
        $(this).addClass('ois-stats-active');
        // Find which graph is being requested
        control = $(this).attr('data-control');
        // Show the appropriate graph
        if (control === 'impression') {
            $('#impressionStats').show();
        } else if (control === 'submission') {
            $('#submissionStats').show();
        } else if (control === 'conversion') {
            $('#conversionRate').show();
        }
    });
});
var conversionData = getConversionRate(submissionData);
console.log(impressionData);
console.log(submissionData);
// However, we need points for all the last 30 days, not just the
// days where there were impressions!
var impressionFullSet = getCompleteDataSet(impressionData);
var submissionFullSet = getCompleteDataSet(submissionData);
var conversionFullSet = getCompleteDataSet(conversionData);
// Now, make a D3.js graph of the data
createBarGraph("#impressionStats", impressionFullSet);
createBarGraph("#submissionStats", submissionFullSet);
createBarGraph("#conversionRate", conversionFullSet);

function getConversionRate(submissionData) {
    var rate = 0;
    var rates = {};
    jQuery.each(submissionData, function(date, value) {
        rate = ((value / impressionData[date])).toFixed(3);
        rates[date] = rate;
    });
    return rates;
} // getConversionRate(JSON)

function getCompleteDataSet(origData) {
    var fullSet = [];
    var today = new Date();
    var year, month, day, time, obj;
    for (var i = 0; i < 30; i++) {
        // 30 day period
        temp = new Date(today.getFullYear(), today.getMonth(), today.getDate() - i);
        year = temp.getFullYear();
        month = ('0' + (temp.getMonth() + 1)).slice(-2);
        day = ('0' + temp.getDate()).slice(-2);
        time = year + "-" + month + "-" + day;
        obj = {};
        obj['date'] = time;
        // if we have this data point, add what we have.
        if (origData.hasOwnProperty(time)) {
            obj['count'] = origData[time];
        } else {
            // otherwise, it's a zero.
            obj['count'] = 0;
        }
        // Add it to our full array of data
        fullSet.push(obj);
    }
    // fullDataSet is exactly the same as origData, but has full data.
    console.log(fullSet);
    return fullSet;
}
/**
 * createBarGraph function.
 * Creates a D3.js chart of the given data.
 *
 * @post the element with id of element param has d3.js chart
 * @param String element
 * @param JSON dataset
 * @return void
 */

function createBarGraph(element, dataset) {
    // Settings for D3.js Graphs
    var margin = {
        top: 20,
        right: 20,
        bottom: 80,
        left: 40
    },
        width = 800 - margin.left - margin.right,
        height = 300 - margin.top - margin.bottom;
    var yLabel = 'Impressions';
    yFormat = d3.format(".0f");
    if (element == '#conversionRate') {
        yLabel = 'Rate';
        yFormat = d3.format(".0%");
    } else if (element == '#submissionStats') {
      yLabel = 'Submissions';
    } // else if
    // Parse the date / time
    var parseDate = d3.time.format("%Y-%m-%d").parse;
    var x = d3.scale.ordinal().rangeRoundBands([width, 0], .05);
    var y = d3.scale.linear().range([height, 0]);
    var xAxis = d3.svg.axis().scale(x).orient("bottom").tickFormat(d3.time.format("%Y-%m-%d"));
    var yAxis = d3.svg.axis().scale(y).orient("left").tickFormat(yFormat).ticks(5);
    // Add a little tooltip
    var tip = d3.tip().attr('class', 'd3-tip').offset([-10, 0]).html(function(d) {
      if (element == '#conversionRate') {
        return "Value: <span style='color:red'>" + (Math.round(d.count * 1000) / 10) + "%</span>";
      } else {
        return "Value: <span style='color:red'>" + d.count + "</span>";
      }
    })
    var svg = d3.select(element).append("svg").attr("width", width + margin.left + margin.right).attr("height", height + margin.top + margin.bottom).append("g").attr("transform", "translate(" + margin.left + "," + margin.top + ")");
    // Add the tooltip
    svg.call(tip);
    dataset.forEach(function(d) {
        d.date = parseDate(d.date);
        d.count = +d.count;
    });
    x.domain(dataset.map(function(d) {
        return d.date;
    }));
    y.domain([0, d3.max(dataset, function(d) {
        return d.count;
    })]);
    svg.append("g").attr("class", "x axis").attr("transform", "translate(0," + height + ")").call(xAxis).selectAll("text").style("text-anchor", "end").attr("dx", "-.8em").attr("dy", "-.55em").attr("transform", "rotate(-90)");
    svg.append("g").attr("class", "y axis").call(yAxis).append("text").attr("transform", "rotate(-90)").attr("y", 6).attr("dy", ".71em").style("text-anchor", "end").text(yLabel);
    svg.selectAll("bar").data(dataset).enter().append("rect").attr("class", "ois-stats-bar").attr("x", function(d) {
        return x(d.date);
    }).on('mouseover', tip.show).on('mouseout', tip.hide).attr("width", x.rangeBand()).attr("y", function(d) {
        return y(d.count);
    }).attr("height", function(d) {
        return height - y(d.count);
    });
}