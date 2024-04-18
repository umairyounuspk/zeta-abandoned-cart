(function( $ ) {
	'use strict';

var recoverable = {
  x: vars.dates,
  y: vars.recoverable,
  type: 'bar',
  name: 'Abandoned',
  marker: {
    color: 'rgb(204,204,204)',
    opacity: 0.5
  }
};

var recovered = {
  x: vars.dates,
  y: vars.recovered,
  type: 'bar',
  name: 'Recovered',
  marker: {
    color: 'rgb(49,130,189)',
    opacity: 0.7,
  }
};

var data = [recoverable, recovered];

var layout = {
  title: {
    text: vars.title
  },
  xaxis: {
    tickangle: -45
  },
  yaxis: {
    dtick: 1
  },
  barmode: 'group'
};

Plotly.newPlot('orderSummaryGraph', data, layout);

})( jQuery );
