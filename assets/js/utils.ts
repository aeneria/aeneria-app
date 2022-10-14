function getBaseUrl(): string {
  return '//app.aeneria.local' //@todo
}

function _urlHandleValueArray(name: string, values: any[]): {name: string, value: string}[] {
  let ret = [] as {name: string, value: string}[]

  const arrayName = name + '[]'
  for (const value of values) {
    ret = ret.concat(_urlHandleValue(arrayName, value))
  }

  return ret
}

function _urlHandleValue(name: string, value: any): {name: string, value: string}[] {
  if (Array.isArray(value)) {
    return _urlHandleValueArray(name, value)
  }
  let queryKeyValue
  if (undefined !== value && null !== value) {
    if (false === value) {
      queryKeyValue = "0"
    }
    else if (true === value) {
      queryKeyValue = "1"
    }
    else {
      queryKeyValue = value.toString()
    }
  }
  else {
    queryKeyValue = ''
  }

  return [{ name: name, value: queryKeyValue }]
}

export function url(path: string, query: null|any) {
    let baseUri = getBaseUrl()
    if (!baseUri.endsWith('/') && !path.startsWith('/')) {
        baseUri += '/'
    }
    if (query) {
        const components = [] as string[]
        let hasComponents = false
        for (let key in query) {
            for (const value of _urlHandleValue(key, query[key])) {
                hasComponents = true
                components.push(encodeURIComponent(value.name) + "=" + encodeURIComponent(value.value))
            }
        }
        if (hasComponents) {
            return baseUri + path + "?" + components.join("&")
        }
        return baseUri + path
    }
    return baseUri + path
}

export function handleFetchError(response: Response) {
  if ('application/json' === response.headers.get('Content-Type')) {
    return response.json().then(body => {
      var _a, _b
      if (body.message) {
        throw body.message
      }
      if (body.error) {
        throw body.error
      }
      if ((_a = body.data) === null || _a === void 0 ? void 0 : _a.message) {
        throw body.data.message
      }
      if ((_b = body.data) === null || _b === void 0 ? void 0 : _b.error) {
        throw body.data.error
      }
      throw `Une erreur s'est produite: ${response.status} - ${response.statusText}`
    })
  }
  else {
    throw `Une erreur s'est produite: ${response.status} - ${response.statusText}`
  }
}

export function queryData(route: string, query: null|any = null) {
  return fetch(url(route, query), {
    method: 'GET',
    mode: 'cors',
    cache: 'default',
    credentials: 'include',
    headers: {Accept: 'application/json'},
    redirect: 'follow',
    referrerPolicy: 'no-referrer',
  })
  .then(response => {
    if (response.ok) {
      return response.json()
    }

    return handleFetchError(response)
  })
}

export function postData(route: string, content: any, method: string, query: any[], jsonify: Boolean = true) {
  return fetch(url(route, query), {
    method: method !== null && method !== void 0 ? method : 'POST',
    mode: 'cors',
    cache: 'default',
    credentials: 'include',
    headers: jsonify ? { 'Accept': 'application/json', 'Content-Type': 'application/json', } : {},
    referrerPolicy: 'no-referrer',
    body: jsonify ? JSON.stringify(content) : content,
  })
  .then(response => {
    if (response.ok) {
      return response.json()
    }

    return handleFetchError(response)
  })
}
