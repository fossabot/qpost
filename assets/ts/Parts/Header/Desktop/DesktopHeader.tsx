/*
 * Copyright (C) 2018-2020 Gigadrive - All rights reserved.
 * https://gigadrivegroup.com
 * https://qpostapp.com
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

import React, {Component} from "react";
import Logo from "../../../../img/navlogo.png";
import Menu from "antd/es/menu";
import "antd/es/mention/style";
import Badge from "antd/es/badge";
import "antd/es/badge/style";
import Layout from "antd/es/layout";
import "antd/es/layout/style";
import NightMode from "../../../NightMode/NightMode";
import {Link} from "react-router-dom";
import Auth from "../../../Auth/Auth";
import BadgeStatus from "../../../Auth/BadgeStatus";

export default class DesktopHeader extends Component<{
	mobile: boolean,
	key: any
}, any> {
	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		const currentUser = Auth.getCurrentUser();
		const notifications = BadgeStatus.getNotifications();
		const messages = BadgeStatus.getMessages();

		return <Layout.Header
			hasSider={true}
			className={"mainNav"}
			style={{
				position: "fixed",
				zIndex: 999,
				width: "100%",
				top: 0,
				display: this.props.mobile ? "none" : "block"
			}}>

			<div className={"container"}>
				{currentUser ? <Link to={"/"} className={"clearUnderline"}>
					<img src={Logo} style={{height: "30px"}} alt={"qpost Logo"}/>
				</Link> : <a href={"/"} className={"clearUnderline"}>
					<img src={Logo} style={{height: "30px"}} alt={"qpost Logo"}/>
				</a>}

				<Menu
					theme={NightMode.isActive() ? "dark" : "light"}
					mode={"horizontal"}
					selectable={false}
					style={{
						lineHeight: "64px",
						float: "right"
					}}>
					{currentUser ? [<Menu.Item key={0}>
						<Link to={"/"} className={"clearUnderline"}>
							home
						</Link>
					</Menu.Item>,

						<Menu.Item key={1} className={"d-none d-lg-inline-block"}>
							<Link to={"/profile/" + currentUser.getUsername()} className={"clearUnderline"}>
								my profile
							</Link>
						</Menu.Item>,

						<Menu.Item key={2}>
							<Link to={"/notifications"} className={"clearUnderline"}>
								notifications{notifications > 0 ?
								<Badge count={notifications} className={"ml-2"}/> : ""}
							</Link>
						</Menu.Item>,

						<Menu.Item key={3}>
							<Link to={"/messages"} className={"clearUnderline"}>
								messages{messages > 0 ? <Badge count={messages} className={"ml-2"}/> : ""}
							</Link>
						</Menu.Item>,

						<Menu.Item key={4}>
							<Link to={"/search"} className={"clearUnderline"}>
								search
							</Link>
						</Menu.Item>,

						<Menu.Item key={4} className={"desktopHeaderUser"}>
							<Link to={"/profile/" + currentUser.getUsername()} className={"clearUnderline"}>
								<img src={currentUser.getAvatarURL()} width={24} height={24}
									 alt={currentUser.getUsername()}
									 className={"rounded"}/><span className={"ml-2"}>{currentUser.getUsername()}</span>
							</Link>
						</Menu.Item>,

						<Menu.Item key={5} className={"desktopHeaderSettings"}>
							<a href={"/settings/preferences/appearance"} className={"clearUnderline"}>
								<i className="fas fa-cog"/>
							</a>
						</Menu.Item>] : <Menu.Item>
						<a href={"/login"} className={"clearUnderline"}>
							log in
						</a>
					</Menu.Item>}
				</Menu>
			</div>
		</Layout.Header>;
	}
}