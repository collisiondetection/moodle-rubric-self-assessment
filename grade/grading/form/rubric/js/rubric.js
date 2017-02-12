M.gradingform_rubric = {};

/**
 * This function is called for each rubric on page.
 */
M.gradingform_rubric.init = function(Y, options) {
    Y.on('click', M.gradingform_rubric.levelclick, '#rubric-'+options.name+' .level', null, Y, options.name);
    // Capture also space and enter keypress.
    Y.on('key', M.gradingform_rubric.levelclick, '#rubric-' + options.name + ' .level', 'space', Y, options.name);
    Y.on('key', M.gradingform_rubric.levelclick, '#rubric-' + options.name + ' .level', 'enter', Y, options.name);

    Y.all('#rubric-'+options.name+' .radio').setStyle('display', 'none')
    Y.all('#rubric-'+options.name+' .level').each(function (node) {
      try {
		  if (node.one('input[type=radio]').get('checked')) {
        	node.addClass('checked');
		  }
      } catch (e){
	  }
    });

	  //console.log("Getting students' self assessment grade");
	  var userid = undefined, param = undefined;

	  function optionalIntParam(paramName, defaultValue){
		  var param = undefined;
		  var re = new RegExp(paramName + "=(\\d+)")
		  if(param = window.location.search.match(re)) {
			  return param[1];
		  }
		  return defaultValue;
	  }

	  function fetchSelfAssessmentData() {
		  var currentUser = Y.one('div[data-region=user-info] a').getAttribute('href');
		  var userid = currentUser.match(/id=(\d+)/)[1];
		  var id = optionalIntParam('id', -1);
		  //console.log(id, userid);
	
		  // remove current user's self assessment
		  var key = Y.one('.key.self_checked');
		  Y.all('.self_checked').removeClass('self_checked');
		  key.addClass('self_checked');	
	
		  if(userid > 0 && id > 0) {
				// get self assessment
				Y.io(options.api, {data: {
						cmd:'get', userid: userid, cmid:id
					}, on: { success: function(id, o, args) {
						var result = Y.JSON.parse(o.responseText);
						if(result.success && result.grades) {
							for(var i = 0; i < result.grades.length; i++) {
								Y.one('#advancedgrading-criteria-' + result.grades[i].criterion + '-levels-' + result.grades[i].level).addClass('self_checked')
							}
						}
						//console.log(result);
					}}
				});
		  }
	  }
	  
	  try {
		  fetchSelfAssessmentData();
	  } catch(e) {
		  // fetch again if user hasn't been graded yet
		  setTimeout(fetchSelfAssessmentData, 500);
	  }
	  
	  

};

M.gradingform_rubric.levelclick = function(e, Y, name) {
    var el = e.target
    while (el && !el.hasClass('level')) el = el.get('parentNode')
    if (!el) return
    e.preventDefault();
    el.siblings().removeClass('checked');

    // Set aria-checked attribute for siblings to false.
    el.siblings().setAttribute('aria-checked', 'false');
    chb = el.one('input[type=radio]')
    if (!chb.get('checked')) {
        chb.set('checked', true)
        el.addClass('checked')
        // Set aria-checked attribute to true if checked.
        el.setAttribute('aria-checked', 'true');
    } else {
        el.removeClass('checked');
        // Set aria-checked attribute to false if unchecked.
        el.setAttribute('aria-checked', 'false');
        el.get('parentNode').all('input[type=radio]').set('checked', false)
    }
}

M.gradingform_rubric.setup_selfassessment = function(Y, options) {

	// set self assessment
	Y.on('click', function(e, Y, options) {
		var el = e.target
		while (el && !el.hasClass('level')) el = el.get('parentNode')
		if (!el) return
		el.siblings().removeClass('self_checked');
		el.addClass('self_checked');
		var parts = e._currentTarget.id.split('-');
		var criteria = parts[2];
		var level = parts[4];

		Y.io(options.api, {data: {
			definitionid: options.definition,
			criterionid: criteria,
			levelid: level
		}, on: {success: function(e) {
			//console.log(e);
		}}});
		//console.log(criteria, level, options.definition);
	}, '.level', null, Y, options);

}
