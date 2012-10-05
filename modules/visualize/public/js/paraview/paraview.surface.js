var midas = midas || {};
midas.visualize = midas.visualize || {};

midas.visualize.renderers = {};

var paraview;

midas.visualize.start = function () {
    // Create a paraview proxy

    if(typeof Paraview != 'function') {
        alert('Unable to connect to the Paraview server. Please contact an administrator.');
        return;
    }

    $('#loadingStatus').html('Creating ParaView session on the server and loading plugins...');
    paraview = new Paraview("/PWService");
    paraview.errorListener = {
        manageError: function(error) {
            //alert('A ParaViewWeb error occurred; check the console for information');
            console.log(error);
            return true;
        }
    };
    paraview.createSession("midas", "surface view", "default");
    paraview.loadPlugins();

    $('#loadingStatus').html('Reading image data from files...');
    paraview.plugins.midascommon.AsyncOpenData(midas.visualize._dataOpened, {
        filename: json.visualize.url,
        otherMeshes: []
    });
}

midas.visualize._dataOpened = function (retVal) {
    $('#loadingStatus').html('Initializing view state and renderer...');
    midas.visualize.imageData = retVal.imageData;
    midas.visualize.input = retVal.input;

    paraview.plugins.midassurface.AsyncInitViewState(midas.visualize.initCallback, {});
    midas.visualize.populateInfo();
};

midas.visualize.populateInfo = function () {
    var bounds = midas.visualize.imageData.Bounds;
    $('#boundsXInfo').html(bounds[0].toFixed(3)+' .. '+bounds[1].toFixed(3));
    $('#boundsYInfo').html(bounds[2].toFixed(3)+' .. '+bounds[3].toFixed(3));
    $('#boundsZInfo').html(bounds[4].toFixed(3)+' .. '+bounds[5].toFixed(3));
    $('#nbPointsInfo').html(midas.visualize.imageData.NbPoints);
    $('#nbCellsInfo').html(midas.visualize.imageData.NbCells);
};

midas.visualize.initCallback = function (retVal) {
    $('#loadingStatus').html('').hide();
    midas.visualize.activeView = retVal.activeView;

    // Create renderers
    midas.visualize.switchRenderer(true);
    $('img.visuLoading').hide();
    $('#renderercontainer').show();
};

midas.visualize.resetCamera = function () {
    if(paraview.plugins.midassurface) {
        paraview.plugins.midassurface.AsyncResetCamera(function () {}, {});
    }
};

midas.visualize.toggleEdges = function () {
    if(paraview.plugins.midassurface) {
        paraview.plugins.midassurface.AsyncToggleEdges(function() {}, {input: midas.visualize.input});
    }
};

midas.visualize.switchRenderer = function (first) {
    if(midas.visualize.renderers.js == undefined) {
        midas.visualize.renderers.js = new JavaScriptRenderer('jsRenderer', '/PWService');
        midas.visualize.renderers.js.init(paraview.sessionId, midas.visualize.activeView.__selfid__);
    }

    if(!first) {
        midas.visualize.renderers.current.unbindToElementId('renderercontainer');
    }
    midas.visualize.renderers.current = midas.visualize.renderers.js;
    midas.visualize.renderers.current.bindToElementId('renderercontainer');
    var el = $('#renderercontainer');
    midas.visualize.renderers.current.setSize(el.width(), el.height());
    midas.visualize.renderers.current.start();
    midas.visualize.renderers.current.updateServerSizeIfNeeded();
};

$(window).load(function () {
    json = $.parseJSON($('div.jsonContent').html());
    midas.visualize.start();
});

$(window).unload(function () {
    paraview.disconnect()
});
