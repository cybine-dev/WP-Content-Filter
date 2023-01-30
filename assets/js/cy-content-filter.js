const cycf_filter = async (event) =>
{
	console.log('test');
	// use document.querySelector(selector) to use css-selectors
	let filterElements = document.getElementsByClassName('cy-content-filter--options');
	for (let filterElement of filterElements)
	{
		if(!filterElement.id.startsWith('filter-options-'))
			continue;

		let responseId = filterElement.id.replace('filter-options-', 'filter-response-');
		let responseElement = document.getElementById(responseId);
		if(!responseElement)
			continue;

		// using js fetch-api to query data
		let result = await fetch(filterElement.getAttribute('action'), 
		{
			method: filterElement.getAttribute('method'), // POST
			body: new FormData(filterElement) // form data
		});

		let content = await result.text();

		// insert data
		responseElement.innerHTML = content;
	}
}