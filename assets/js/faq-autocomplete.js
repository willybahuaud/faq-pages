/**
 * Autocompletion pour le champ de recherche FAQ.
 *
 * Interroge l'API REST WordPress au fur et a mesure de la frappe
 * avec debounce et navigation clavier.
 *
 * @package FAQ_Pages
 */

/* global afpAutocomplete */

( function () {
	'use strict';

	var input;
	var suggestionsContainer;
	var abortController;
	var debouncedFetch;
	var activeIndex = -1;
	var MIN_CHARS = 3;
	var DEBOUNCE_DELAY = 300;

	/**
	 * Initialise l'autocompletion sur le champ de recherche FAQ.
	 *
	 * @return {void}
	 */
	function afpInit() {
		input = document.getElementById( 'afp-search-input' );

		if ( ! input ) {
			return;
		}

		suggestionsContainer = input.parentElement.querySelector( '.afp-suggestions' );

		if ( ! suggestionsContainer ) {
			return;
		}

		debouncedFetch = afpDebounce( afpFetchSuggestions, DEBOUNCE_DELAY );

		input.addEventListener( 'input', afpOnInput );
		input.addEventListener( 'keydown', afpOnKeyDown );
		document.addEventListener( 'click', afpOnClickOutside );

		input.setAttribute( 'role', 'combobox' );
		input.setAttribute( 'aria-autocomplete', 'list' );
		input.setAttribute( 'aria-controls', 'afp-suggestions-list' );
		input.setAttribute( 'aria-expanded', 'false' );

		suggestionsContainer.setAttribute( 'id', 'afp-suggestions-list' );
	}

	/**
	 * Callback sur l'evenement input du champ de recherche.
	 *
	 * @return {void}
	 */
	function afpOnInput() {
		var query = input.value.trim();

		if ( query.length < MIN_CHARS ) {
			afpClearSuggestions();
			return;
		}

		afpRenderLoading();
		debouncedFetch( query );
	}

	/**
	 * Retourne une version debouncee d'une fonction.
	 *
	 * @param {Function} callback La fonction a debouncer.
	 * @param {number}   delay    Le delai en millisecondes.
	 * @return {Function} La fonction debouncee.
	 */
	function afpDebounce( callback, delay ) {
		var timer;

		return function afpDebouncedCallback( query ) {
			clearTimeout( timer );
			timer = setTimeout( function afpDebouncedTimer() {
				callback( query );
			}, delay );
		};
	}

	/**
	 * Verifie qu'une URL est interne au site (meme origine).
	 *
	 * @param {string} url L'URL a verifier.
	 * @return {boolean} True si l'URL est valide et interne.
	 */
	function afpIsValidUrl( url ) {
		try {
			var parsed = new URL( url, window.location.origin );
			return parsed.origin === window.location.origin;
		} catch ( e ) {
			return false;
		}
	}

	/**
	 * Appelle l'API REST pour recuperer les suggestions.
	 *
	 * @param {string} query Le terme de recherche.
	 * @return {void}
	 */
	function afpFetchSuggestions( query ) {
		if ( abortController ) {
			abortController.abort();
		}

		abortController = new AbortController();

		var url = afpAutocomplete.restUrl + '?search=' + encodeURIComponent( query ) + '&per_page=5&_fields=id,title,link';

		fetch( url, {
			signal: abortController.signal,
		} )
			.then( afpHandleResponse )
			.then( afpRenderSuggestions )
			.catch( afpHandleFetchError );
	}

	/**
	 * Traite la reponse HTTP de l'API.
	 *
	 * @param {Response} response La reponse fetch.
	 * @return {Promise} La promesse avec les donnees JSON.
	 */
	function afpHandleResponse( response ) {
		if ( ! response.ok ) {
			throw new Error( response.statusText );
		}
		return response.json();
	}

	/**
	 * Gere les erreurs de fetch (hors abort).
	 *
	 * @param {Error} error L'erreur capturee.
	 * @return {void}
	 */
	function afpHandleFetchError( error ) {
		if ( error.name === 'AbortError' ) {
			return;
		}
		afpRenderError();
	}

	/**
	 * Affiche les suggestions dans le dropdown via DOM API.
	 *
	 * Utilise createElement/textContent au lieu de innerHTML
	 * pour eviter tout risque de XSS via title.rendered.
	 *
	 * @param {Array} results Les resultats de l'API REST.
	 * @return {void}
	 */
	function afpRenderSuggestions( results ) {
		activeIndex = -1;

		if ( ! results.length ) {
			afpRenderNoResults();
			return;
		}

		suggestionsContainer.innerHTML = '';

		var i;
		for ( i = 0; i < results.length; i++ ) {
			var link = document.createElement( 'a' );
			link.href = results[ i ].link;
			link.className = 'afp-suggestion-item';
			link.setAttribute( 'role', 'option' );
			link.setAttribute( 'aria-selected', 'false' );
			link.setAttribute( 'id', 'afp-suggestion-' + i );
			link.textContent = results[ i ].title.rendered;
			link.addEventListener( 'click', afpOnSuggestionClick );
			suggestionsContainer.appendChild( link );
		}

		suggestionsContainer.removeAttribute( 'hidden' );
		input.setAttribute( 'aria-expanded', 'true' );
	}

	/**
	 * Affiche l'etat "chargement" dans le dropdown.
	 *
	 * @return {void}
	 */
	function afpRenderLoading() {
		suggestionsContainer.innerHTML = '';
		var div = document.createElement( 'div' );
		div.className = 'afp-loading';
		div.setAttribute( 'role', 'status' );
		div.textContent = afpAutocomplete.loading;
		suggestionsContainer.appendChild( div );
		suggestionsContainer.removeAttribute( 'hidden' );
		input.setAttribute( 'aria-expanded', 'true' );
	}

	/**
	 * Affiche le message "aucun resultat".
	 *
	 * @return {void}
	 */
	function afpRenderNoResults() {
		suggestionsContainer.innerHTML = '';
		var div = document.createElement( 'div' );
		div.className = 'afp-no-results';
		div.textContent = afpAutocomplete.noResults;
		suggestionsContainer.appendChild( div );
		suggestionsContainer.removeAttribute( 'hidden' );
		input.setAttribute( 'aria-expanded', 'true' );
	}

	/**
	 * Affiche le message d'erreur.
	 *
	 * @return {void}
	 */
	function afpRenderError() {
		suggestionsContainer.innerHTML = '';
		var div = document.createElement( 'div' );
		div.className = 'afp-error';
		div.textContent = afpAutocomplete.error;
		suggestionsContainer.appendChild( div );
		suggestionsContainer.removeAttribute( 'hidden' );
		input.setAttribute( 'aria-expanded', 'true' );
	}

	/**
	 * Vide et masque le dropdown de suggestions.
	 *
	 * @return {void}
	 */
	function afpClearSuggestions() {
		suggestionsContainer.innerHTML = '';
		suggestionsContainer.setAttribute( 'hidden', '' );
		input.setAttribute( 'aria-expanded', 'false' );
		input.removeAttribute( 'aria-activedescendant' );
		activeIndex = -1;
	}

	/**
	 * Redirige vers la page de detail au clic sur une suggestion.
	 *
	 * @param {Event} event L'evenement click.
	 * @return {void}
	 */
	function afpOnSuggestionClick( event ) {
		event.preventDefault();
		var href = event.currentTarget.getAttribute( 'href' );
		if ( href && afpIsValidUrl( href ) ) {
			window.location.href = href;
		}
	}

	/**
	 * Ferme le dropdown si clic hors de la zone de recherche.
	 *
	 * @param {Event} event L'evenement click.
	 * @return {void}
	 */
	function afpOnClickOutside( event ) {
		if ( ! input ) {
			return;
		}
		var wrapper = input.closest( '.afp-search-wrapper' );
		if ( wrapper && ! wrapper.contains( event.target ) ) {
			afpClearSuggestions();
		}
	}

	/**
	 * Gere la navigation clavier dans les suggestions.
	 *
	 * Fleche bas/haut : naviguer, Entree : selectionner, Echap : fermer.
	 *
	 * @param {KeyboardEvent} event L'evenement keydown.
	 * @return {void}
	 */
	function afpOnKeyDown( event ) {
		var items = suggestionsContainer.querySelectorAll( '.afp-suggestion-item' );

		if ( ! items.length ) {
			return;
		}

		if ( event.key === 'ArrowDown' ) {
			event.preventDefault();
			activeIndex = ( activeIndex + 1 ) % items.length;
			afpUpdateActiveItem( items );
		} else if ( event.key === 'ArrowUp' ) {
			event.preventDefault();
			activeIndex = activeIndex <= 0 ? items.length - 1 : activeIndex - 1;
			afpUpdateActiveItem( items );
		} else if ( event.key === 'Enter' && activeIndex >= 0 ) {
			event.preventDefault();
			var href = items[ activeIndex ].getAttribute( 'href' );
			if ( href && afpIsValidUrl( href ) ) {
				window.location.href = href;
			}
		} else if ( event.key === 'Escape' ) {
			afpClearSuggestions();
			input.blur();
		}
	}

	/**
	 * Met a jour l'item actif visuellement et pour l'accessibilite.
	 *
	 * @param {NodeList} items Les elements de suggestion.
	 * @return {void}
	 */
	function afpUpdateActiveItem( items ) {
		var i;
		for ( i = 0; i < items.length; i++ ) {
			items[ i ].setAttribute( 'aria-selected', i === activeIndex ? 'true' : 'false' );
		}
		if ( items[ activeIndex ] ) {
			input.setAttribute( 'aria-activedescendant', items[ activeIndex ].id );
		}
	}

	document.addEventListener( 'DOMContentLoaded', afpInit );
} )();
