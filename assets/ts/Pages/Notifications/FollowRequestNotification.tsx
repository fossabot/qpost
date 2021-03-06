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
import Notification from "../../Entity/Feed/Notification";
import {Card} from "antd";
import {Link} from "react-router-dom";
import VerifiedBadge from "../../Component/VerifiedBadge";
import TimeAgo from "../../Component/TimeAgo";
import FollowerListItem from "../../Component/User/FollowerListItem";

export default class FollowRequestNotification extends Component<{
	notification: Notification
}, {
	clear: boolean
}> {
	constructor(props) {
		super(props);

		this.state = {
			clear: false
		}
	}

	render(): React.ReactElement<any, string | React.JSXElementConstructor<any>> | string | number | {} | React.ReactNodeArray | React.ReactPortal | boolean | null | undefined {
		if (this.state.clear) return "";

		const notification = this.props.notification;
		const user = notification.getReferencedUser();
		const followRequest = notification.getReferencedFollowRequest();

		if (followRequest && user) {
			return <Card size={"small"} className={!notification.isSeen() ? "unseenNotification" : ""}>
				<p className={"mb-0"}>
					<i className={"fas fa-user-plus text-info"}/> <Link to={"/profile/" + user.getUsername()}
																		className={"font-weight-bold clearUnderline"}><img
					src={user.getAvatarURL()} width={24} height={24} className={"rounded mr-1"}
					alt={user.getUsername()}/>{user.getDisplayName()}</Link><VerifiedBadge target={user}/> requested to
					follow you.<span className={"text-muted ml-2"}><i className="far fa-clock"/> <TimeAgo
					time={notification.getTime()} short={true}/></span>
				</p>

				<hr/>

				<FollowerListItem user={user} requestId={followRequest.getId()} notificationParent={this}/>
			</Card>;
		} else {
			return "";
		}
	}
}