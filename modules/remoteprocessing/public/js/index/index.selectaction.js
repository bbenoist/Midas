$('img#processButtonImg').show();
$('img#processButtonLoadiing').hide();

$('#blockDashboardLink').click(function(){window.location.replace($('.webroot').val()+$('#customDashboardUrl').val())});
$('#blockManageScheduledLink').click(function(){window.location.replace($('.webroot').val()+'/remoteprocessing/job/manage');});
$('#blockCreateLink').click(function(){window.location.replace($('.webroot').val()+'/remoteprocessing/job/init');});
$('#blockCreateScheduledLink').click(function(){window.location.replace($('.webroot').val()+'/remoteprocessing/job/init?scheduled=true');});