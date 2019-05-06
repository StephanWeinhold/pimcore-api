⛔️ DEPRECATED 

# This is no longer supported - pimcore has multiple great APIs now.

## pimcore-api
### API-functionality for pimcore to return object data

#### pimcore

You will need to ad a 
<a href="https://www.pimcore.org/wiki/pages/viewpage.action?pageId=14551636" target="_blank">static-route</a> 
to your pimcore that looks like this one:

<table>
<tr>
<th>Pattern</th>
<th>Reverse</th>
<th>Controller</th>
<th>Action</th>
<th>Variables</th>
</tr>
<tr>
<td>/([a-z]+)\/([0-9]+)(\/[a-z]+)?(\/[a-z]{2})?/</td>
<td>/api/%objectType/%articleNum{/%returnType}{/%language}</td>
<td>api</td>
<td>default</td>
<td>objectType,articleNum,returnType,language</td>
</tr>
</table>

#### Calling the API

The API is based on REST. So you can receive data via a simple URL-call. The most basic way to call it is:

<pre>https://YOUR_URL/OBJECT_TYPE/OBJECT_ID/?apiKey=YOUR_API_KEY</pre>

This will return an UTF-8 encoded JSON-object.

#### Params

The API can handle several params to customize the results.

##### returnType (URL, GET, POST)
The type of the return. You can choose between JSON and XML. Default-value is JSON.
You can either set this param via the URL-path:

<pre>https://YOUR_URL/OBJECT_TYPE/OBJECT_ID/RETURN_TYPE/?apiKey=YOUR_API_KEY</pre>

…or add it as a GET-param:

<pre>https://YOUR_URL/OBJECT_TYPE/OBJECT_ID/?returnType=RETURN_TYPE&apiKey=YOUR_API_KEY</pre>

…or send it via POST.

##### language (URL, GET, POST)
If the chosen language exists, the data will be returned in this language. 
Else you will receive them in the fallback language ('de'). 
To set this param you can either set it via the URL-path:

<pre>https://YOUR_URL/OBJECT_TYPE/OBJECT_ID/LANGUAGE/?apiKey=YOUR_API_KEY</pre>

…or add it as a GET-param:

<pre>https://YOUR_URL/OBJECT_TYPE/OBJECT_ID/?returnType=LANGUAGE&apiKey=YOUR_API_KEY</pre>

…or send it via POST.

##### att (GET, POST)
With this param you can choose which attributes you want to be returned.
To set this param you can either add it as a GET-param:

<pre>https://YOUR_URL/OBJECT_TYPE/OBJECT_ID/?att[0]=ATTRIBUTE_1&att[1]=ATTRIBUTE_1&apiKey=YOUR_API_KEY</pre>

…or send it via POST.
