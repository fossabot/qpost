/*
 * Copyright (C) 2018-2019 Gigadrive - All rights reserved.
 * https://gigadrivegroup.com
 * https://qpo.st
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://gnu.org/licenses/>
 */

import "./electron";
import React from "react";
import ReactDOM from "react-dom";
import App from "./App";
import "../scss/index.scss";
import $ from "jquery";
import "bootstrap";
import ErrorBoundary from "./ErrorBoundary";
import "./registerServiceWorker";

window["jQuery"] = $;
window["$"] = $;

App.init();

ReactDOM.render(<ErrorBoundary>
	<App/>
</ErrorBoundary>, document.getElementById("root"));
